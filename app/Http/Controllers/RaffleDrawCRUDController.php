<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\RaffleDraw;
use App\Models\RaffleTicket;
use App\Services\RaffleWinnerSelector;
use Illuminate\Http\RedirectResponse;
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

    public function pickWinner(RaffleDraw $draw, RaffleWinnerSelector $selector): RedirectResponse
    {
        Gate::authorize('admin');

        [$winnerName, $drawName] = DB::transaction(function () use ($draw, $selector) {
            $lockedDraw = RaffleDraw::lockForUpdate()->findOrFail($draw->id);

            abort_if($lockedDraw->winner_enrollment_id !== null, 422, 'Vencedor já selecionado');
            abort_if(! $lockedDraw->is_active, 422, 'Sorteio inativo');

            $tickets = $lockedDraw->tickets()->get(['enrollment_id', 'quantity']);

            abort_if((int) $tickets->sum('quantity') < 1, 422, 'Nenhum bilhete vendido');

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
}
