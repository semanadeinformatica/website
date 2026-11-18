<?php

namespace App\Services;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

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
     * Net points won today for a game (payout minus the winning wager).
     * Losing sessions do not count and a negative net is never subtracted.
     */
    public function netWonToday(int $enrollmentId, ArcadeGame $game): int
    {
        return (int) ArcadeSession::wonToday($enrollmentId, $game->id)
            ->sum(DB::raw('CASE WHEN COALESCE(payout, 0) > wager THEN COALESCE(payout, 0) - wager ELSE 0 END'));
    }

    /**
     * Remaining net allowance for the day, or null when the game is uncapped.
     */
    public function remainingDailyAllowance(int $enrollmentId, ArcadeGame $game): ?int
    {
        if ($game->daily_win_cap === null) {
            return null;
        }

        return max(0, $game->daily_win_cap - $this->netWonToday($enrollmentId, $game));
    }

    /**
     * Clamp a winning payout so its net gain stays within the daily cap.
     * The payout never drops below the wager, so a win cannot become a loss.
     */
    public function payoutWithinDailyCap(
        Enrollment $enrollment,
        ArcadeGame $game,
        int $wager,
        int $payout,
    ): int {
        $remaining = $this->remainingDailyAllowance($enrollment->id, $game);

        if ($remaining === null || $payout <= 0) {
            return $payout;
        }

        return min($payout, $wager + $remaining);
    }

    /**
     * Check and enforce the daily net win cap.
     * $additionalNet is the potential net gain of the bet being placed.
     */
    public function checkDailyWinCap(int $enrollmentId, ArcadeGame $game, int $additionalNet = 0): void
    {
        if ($game->daily_win_cap === null) {
            return;
        }

        $netToday = $this->netWonToday($enrollmentId, $game);

        abort_if(
            $netToday >= $game->daily_win_cap || ($additionalNet > 0 && ($netToday + $additionalNet) > $game->daily_win_cap),
            422,
            'Limite diário atingido'
        );
    }

    /**
     * Bets placed by an enrollment today for a game.
     * A coin-flip challenger's bet is counted on the join date.
     */
    public function betsToday(int $enrollmentId, ArcadeGame $game): int
    {
        return ArcadeSession::query()
            ->where('arcade_game_id', $game->id)
            ->where(function ($query) use ($enrollmentId) {
                $query->where(function ($creator) use ($enrollmentId) {
                    $creator->where('enrollment_id', $enrollmentId)
                        ->whereDate('started_at', today());
                })->orWhere(function ($challenger) use ($enrollmentId) {
                    $challenger->where('challenger_enrollment_id', $enrollmentId)
                        ->whereDate('resolved_at', today());
                });
            })
            ->count();
    }

    /**
     * Check and enforce the daily bet-count limit.
     */
    public function checkDailyBetLimit(int $enrollmentId, ArcadeGame $game): void
    {
        if ($game->daily_bet_limit === null) {
            return;
        }

        abort_if(
            $this->betsToday($enrollmentId, $game) >= $game->daily_bet_limit,
            422,
            'Limite diário de apostas atingido'
        );
    }
}
