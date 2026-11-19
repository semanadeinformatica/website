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

    protected $load = ['winner.participant.user', 'required_event'];

    protected array $rules = [
        'edition_id' => 'required|exists:editions,id',
        'required_event_id' => 'nullable|exists:events,id',
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
            'events' => Event::with('event_day:id,edition_id')->orderBy('name')->get(['id', 'name', 'event_day_id']),
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

    protected function created(array $new): ?array
    {
        if (! empty($new['required_event_id'])) {
            $event = Event::with('event_day')->findOrFail($new['required_event_id']);
            abort_if(
                (int) $event->event_day?->edition_id !== (int) $new['edition_id'],
                422,
                'O evento obrigatório tem de pertencer à mesma edição do sorteio.'
            );
        }

        return $new;
    }

    protected function updated($old, array $new): ?array
    {
        abort_if(
            (int) $new['edition_id'] !== (int) $old->edition_id,
            422,
            'A edição de um sorteio não pode ser alterada.'
        );

        if (! empty($new['required_event_id'])) {
            $event = Event::with('event_day')->findOrFail($new['required_event_id']);
            abort_if(
                (int) $event->event_day?->edition_id !== (int) $new['edition_id'],
                422,
                'O evento obrigatório tem de pertencer à mesma edição do sorteio.'
            );
        }

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

        $drawsInEdition = RaffleDraw::with('required_event')
            ->where('edition_id', $editionId)
            ->orderBy('id')
            ->get();

        $requestedDrawId = $request->query('draw_id');
        $selectedDraw = null;
        if ($requestedDrawId) {
            $selectedDraw = $drawsInEdition->firstWhere('id', (int) $requestedDrawId);
        }
        if (! $selectedDraw) {
            $selectedDraw = $drawsInEdition->firstWhere('winner_enrollment_id', null) ?? $drawsInEdition->first();
        }

        $alreadyWonEnrollmentIds = RaffleDraw::where('edition_id', $editionId)
            ->whereNotNull('winner_enrollment_id')
            ->when($selectedDraw, fn ($q) => $q->where('id', '!=', $selectedDraw->id))
            ->pluck('winner_enrollment_id');

        $eligibleQuery = Enrollment::query()
            ->where('edition_id', $editionId)
            ->where('points', '>', 0)
            ->whereNotIn('id', $alreadyWonEnrollmentIds);

        if ($selectedDraw && $selectedDraw->required_event_id) {
            $requiredEventId = $selectedDraw->required_event_id;
            $eligibleQuery->whereHas('quests', fn ($q) => $q->where('quests.requirement_type', Event::class)->where('quests.requirement_id', $requiredEventId));
            $requiredEventName = $selectedDraw->required_event?->name;
        } else {
            $requiredEventName = null;
        }

        $hasPurchasedTickets = (bool) ($selectedDraw && $selectedDraw->tickets()->exists());

        if ($hasPurchasedTickets) {
            $eligibleEnrollmentIds = (clone $eligibleQuery)->pluck('id');
            $purchasedTickets = $selectedDraw->tickets()
                ->whereIn('enrollment_id', $eligibleEnrollmentIds)
                ->with('enrollment.participant.user')
                ->get();

            $eligibleCount = $purchasedTickets->count();
            $totalTickets = (int) $purchasedTickets->sum('quantity');

            $eligibleParticipants = $purchasedTickets
                ->sortByDesc('quantity')
                ->values()
                ->map(fn ($ticket) => [
                    'id' => $ticket->enrollment_id,
                    'name' => $ticket->enrollment?->participant?->user?->name ?? "Participante #{$ticket->enrollment_id}",
                    'email' => $ticket->enrollment?->participant?->user?->email,
                    'points' => (int) ($ticket->enrollment?->points ?? 0),
                    'tickets' => (int) $ticket->quantity,
                ]);
        } else {
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
                    'tickets' => (int) $e->points,
                ]);
        }

        $data['selectedDrawId'] = $selectedDraw?->id;
        $data['selectedDrawName'] = $selectedDraw?->name;
        $data['requiredEventName'] = $requiredEventName;
        $data['eligibleCount'] = $eligibleCount;
        $data['totalTickets'] = $totalTickets;
        $data['eligibleParticipants'] = $eligibleParticipants;
        $data['activeEditionId'] = $editionId;
        $data['isPointsBased'] = ! $hasPurchasedTickets;

        return $data;
    }

    public function pickWinner(RaffleDraw $draw, RaffleWinnerSelector $selector): RedirectResponse
    {
        Gate::authorize('admin');

        [$winnerName, $drawName] = DB::transaction(function () use ($draw, $selector) {
            $editionId = $draw->edition_id;
            Edition::lockForUpdate()->findOrFail($editionId);

            $lockedDraw = RaffleDraw::lockForUpdate()->findOrFail($draw->id);

            abort_if($lockedDraw->winner_enrollment_id !== null, 422, 'Vencedor já selecionado');
            abort_if(! $lockedDraw->is_active, 422, 'Sorteio inativo');

            // Exclude anyone who already won ANY raffle in this edition
            $alreadyWonEnrollmentIds = RaffleDraw::where('edition_id', $editionId)
                ->where('id', '!=', $lockedDraw->id)
                ->whereNotNull('winner_enrollment_id')
                ->pluck('winner_enrollment_id');

            $eligibleQuery = Enrollment::query()
                ->where('edition_id', $editionId)
                ->where('points', '>', 0)
                ->whereNotIn('id', $alreadyWonEnrollmentIds);

            if ($lockedDraw->required_event_id) {
                $requiredEventId = $lockedDraw->required_event_id;
                $eligibleQuery->whereHas('quests', fn ($q) => $q->where('quests.requirement_type', Event::class)->where('quests.requirement_id', $requiredEventId));
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
