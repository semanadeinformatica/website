<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\RaffleDraw;
use App\Models\RaffleTicket;
use App\Services\RaffleWinnerSelector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class RaffleDrawCRUDController extends CRUDController
{
    protected string $model = RaffleDraw::class;

    protected string $view = 'RaffleDraws';

    protected $load = ['winner.participant.user'];

    protected array $rules = [
        'edition_id' => 'required|exists:editions,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'ticket_price' => 'required|integer|min:1',
        'is_active' => 'boolean',
        'draw_at' => 'nullable|date|after:now',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }

    protected function updateRules($old): array
    {
        $rules = $this->rules;

        if ($old->winner_enrollment_id !== null || ! $old->is_active) {
            $rules['draw_at'] = 'nullable|date';
        }

        return $rules;
    }

    protected function updated($old, array $new): ?array
    {
        abort_if(
            (int) $new['edition_id'] !== (int) $old->edition_id,
            422,
            'A edição de um sorteio não pode ser alterada.'
        );

        return $new;
    }

    protected function destroyed($old): bool
    {
        if (($old['winner_enrollment_id'] ?? null) !== null) {
            return false;
        }

        return ! RaffleTicket::where('raffle_draw_id', $old['id'] ?? null)->exists();
    }

    public function destroy($id)
    {
        $deleted = DB::transaction(function () use ($id) {
            $draw = RaffleDraw::lockForUpdate()->findOrFail($id);

            if (! $this->destroyed($draw->toArray())) {
                return false;
            }

            $draw->delete();

            return true;
        });

        if (! $deleted) {
            return redirect()->back()->dangerBanner(
                'Não é possível eliminar um sorteio com bilhetes vendidos ou vencedor.'
            );
        }

        Log::alert('Raffle draw deleted', ['raffle_draw_id' => $id]);

        return redirect()->back();
    }

    protected function getIndexData(Request $request): array
    {
        $data = parent::getIndexData($request);

        $requestedEdition = $request->query('edition_id');
        $editionId = ($requestedEdition && $requestedEdition !== 'all')
            ? (int) $requestedEdition
            : (Edition::orderByDesc('year')->value('id') ?? 1);

        $closingEvent = Event::whereHas('event_day', fn ($q) => $q->where('edition_id', $editionId))
            ->where(function ($q) {
                $q->where('name', 'ilike', '%Sessão de Encerramento%')
                  ->orWhere('name', 'ilike', '%Encerramento%');
            })
            ->first();

        if (! $closingEvent) {
            $closingEvent = Event::whereHas('event_day', fn ($q) => $q->where('edition_id', $editionId))
                ->orderByDesc('event_day_id')
                ->orderByDesc('time_start')
                ->first();
        }

        $alreadyWonEnrollmentIds = RaffleDraw::where('edition_id', $editionId)
            ->whereNotNull('winner_enrollment_id')
            ->pluck('winner_enrollment_id');

        $eligibleQuery = Enrollment::query()
            ->where('edition_id', $editionId)
            ->where('points', '>', 0)
            ->whereNotIn('id', $alreadyWonEnrollmentIds);

        if ($closingEvent) {
            $eligibleQuery->where(function ($query) use ($closingEvent) {
                $query->whereHas('events', fn ($q) => $q->where('events.id', $closingEvent->id))
                      ->orWhereHas('quests', fn ($q) => $q->where('quests.requirement_type', Event::class)->where('quests.requirement_id', $closingEvent->id));
            });
        }

        $eligibleCount = (clone $eligibleQuery)->count();
        $totalTickets = (int) (clone $eligibleQuery)->sum('points');

        $eligibleParticipants = (clone $eligibleQuery)
            ->with('participant.user')
            ->orderByDesc('points')
            ->get()
            ->map(fn (Enrollment $e) => [
                'id' => $e->id,
                'name' => $e->participant?->user?->name ?? "Participante #{$e->id}",
                'email' => $e->participant?->user?->email,
                'points' => (int) $e->points,
            ]);

        $data['eligibleCount'] = $eligibleCount;
        $data['totalTickets'] = $totalTickets;
        $data['eligibleParticipants'] = $eligibleParticipants;
        $data['closingEventName'] = $closingEvent?->name ?? 'Sessão de Encerramento';
        $data['activeEditionId'] = $editionId;

        return $data;
    }

    public function pickWinner(RaffleDraw $draw, RaffleWinnerSelector $selector): RedirectResponse
    {
        Gate::authorize('admin');

        [$winnerName, $drawName] = DB::transaction(function () use ($draw, $selector) {
            $lockedDraw = RaffleDraw::lockForUpdate()->findOrFail($draw->id);

            abort_if($lockedDraw->winner_enrollment_id !== null, 422, 'Vencedor já selecionado');
            abort_if(! $lockedDraw->is_active, 422, 'Sorteio inativo');

            $editionId = $lockedDraw->edition_id;

            $closingEvent = Event::whereHas('event_day', fn ($q) => $q->where('edition_id', $editionId))
                ->where(function ($q) {
                    $q->where('name', 'ilike', '%Sessão de Encerramento%')
                      ->orWhere('name', 'ilike', '%Encerramento%');
                })
                ->first();

            if (! $closingEvent) {
                $closingEvent = Event::whereHas('event_day', fn ($q) => $q->where('edition_id', $editionId))
                    ->orderByDesc('event_day_id')
                    ->orderByDesc('time_start')
                    ->first();
            }

            // Exclude anyone who already won ANY raffle in this edition
            $alreadyWonEnrollmentIds = RaffleDraw::where('edition_id', $editionId)
                ->where('id', '!=', $lockedDraw->id)
                ->whereNotNull('winner_enrollment_id')
                ->pluck('winner_enrollment_id');

            $eligibleQuery = Enrollment::query()
                ->where('edition_id', $editionId)
                ->where('points', '>', 0)
                ->whereNotIn('id', $alreadyWonEnrollmentIds);

            if ($closingEvent) {
                $eligibleQuery->where(function ($query) use ($closingEvent) {
                    $query->whereHas('events', fn ($q) => $q->where('events.id', $closingEvent->id))
                          ->orWhereHas('quests', fn ($q) => $q->where('quests.requirement_type', Event::class)->where('quests.requirement_id', $closingEvent->id));
                });
            }

            // If purchased tickets exist for this draw, respect them (filtering by eligibility):
            $hasPurchasedTickets = $lockedDraw->tickets()->exists();

            if ($hasPurchasedTickets) {
                $tickets = $lockedDraw->tickets()
                    ->whereIn('enrollment_id', $eligibleQuery->pluck('id'))
                    ->get(['enrollment_id', 'quantity']);
            } else {
                $eligibleEnrollments = $eligibleQuery->get(['id', 'points']);
                $tickets = $eligibleEnrollments->map(fn ($e) => new RaffleTicket([
                    'enrollment_id' => $e->id,
                    'quantity' => (int) $e->points,
                ]));
            }

            abort_if((int) $tickets->sum('quantity') < 1, 422, 'Nenhum participante elegível para o sorteio.');

            $winnerId = $selector->select($tickets);
            $winner = Enrollment::with('participant.user')->findOrFail($winnerId);

            $lockedDraw->update([
                'winner_enrollment_id' => $winnerId,
                'is_active' => false,
                'draw_at' => now(),
            ]);

            $winnerName = $winner->participant?->user?->name ?? "Participante #{$winnerId}";

            return [$winnerName, $lockedDraw->name];
        });

        Log::info('Raffle draw winner selected', [
            'draw' => $drawName,
            'winner' => $winnerName,
            'draw_id' => $draw->id,
        ]);

        return redirect()->back()->banner("Vencedor selecionado: {$winnerName}!");
    }

    public function resetWinner(RaffleDraw $draw): RedirectResponse
    {
        Gate::authorize('admin');

        $draw->update([
            'winner_enrollment_id' => null,
            'is_active' => true,
            'draw_at' => null,
        ]);

        Log::info('Raffle draw winner reset', [
            'draw_id' => $draw->id,
        ]);

        return redirect()->back()->banner('Vencedor anulado. O prémio pode ser sorteado novamente.');
    }
}
