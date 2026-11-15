<?php

namespace App\Services;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Enrollment;

class ArcadeWagerService
{
    /**
     * Deduct wager from enrollment inside a locked transaction.
     * Returns the locked Enrollment model for use within the same transaction.
     * MUST be called from within DB::transaction().
     */
    public function deductWager(int $enrollmentId, int $wager): Enrollment
    {
        $enrollment = Enrollment::lockForUpdate()->findOrFail($enrollmentId);
        abort_if($enrollment->points < $wager, 422, 'Saldo insuficiente');
        abort_if($wager < 1, 422, 'Aposta inválida');
        $enrollment->decrement('points', $wager);

        return $enrollment;
    }

    /**
     * Credit payout to enrollment. MUST be called from within DB::transaction().
     */
    public function creditPayout(Enrollment $enrollment, int $payout): void
    {
        if ($payout > 0) {
            $enrollment->increment('points', $payout);
        }
    }

    /**
     * Refund wager to enrollment. MUST be called from within DB::transaction().
     */
    public function refundWager(int $enrollmentId, int $wager): Enrollment
    {
        $enrollment = Enrollment::lockForUpdate()->findOrFail($enrollmentId);
        if ($wager > 0) {
            $enrollment->increment('points', $wager);
        }

        return $enrollment;
    }

    /**
     * Check and enforce the daily win cap for skill games.
     * Throws 422 if already at or exceeding cap.
     */
    public function checkDailyWinCap(int $enrollmentId, ArcadeGame $game, int $additionalPayout = 0): void
    {
        if ($game->daily_win_cap === null) {
            return;
        }

        $wonToday = (int) ArcadeSession::wonToday($enrollmentId, $game->id)->sum('payout');
        abort_if(
            $wonToday >= $game->daily_win_cap || ($additionalPayout > 0 && ($wonToday + $additionalPayout) > $game->daily_win_cap),
            422,
            'Limite diário atingido'
        );
    }
}
