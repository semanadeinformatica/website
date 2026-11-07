<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\RaffleDraw;
use App\Services\RaffleWinnerSelector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class RaffleDrawCRUDController extends CRUDController
{
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

            return [$winner->participant->user->name, $lockedDraw->name];
        });

        Log::info('Raffle draw winner selected', [
            'draw' => $drawName,
            'winner' => $winnerName,
            'draw_id' => $draw->id,
        ]);

        return redirect()->back()->banner("Vencedor selecionado: {$winnerName}!");
    }
}
