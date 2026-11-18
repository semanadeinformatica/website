<?php

namespace App\Services;

use App\Models\ArcadeNetFlowReview;
use App\Models\Enrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArcadeNetFlowService
{
    public const RISK_HIGH = 'high';

    public const RISK_MEDIUM = 'medium';

    public const RISK_LOW = 'low';

    public const REVIEW_UNREVIEWED = 'unreviewed';

    /**
     * A pair is considered high risk when it has a meaningful sample,
     * an overwhelmingly one-sided result, and a large one-way flow.
     */
    private const HIGH_MIN_MATCHES = 5;

    private const HIGH_MIN_WIN_SHARE = 0.8;

    private const HIGH_MIN_NET_FLOW = 500;

    private const MEDIUM_MIN_MATCHES = 3;

    private const MEDIUM_MIN_WIN_SHARE = 0.75;

    private const MEDIUM_MIN_NET_FLOW = 200;

    /**
     * Aggregate resolved PvP sessions into one row per account pair.
     *
     * The flow is measured as the net amount of wagers one side lost to the
     * other. For example, if A lost 300 pts to B and B lost 100 pts to A, the
     * net flow is 200 pts from A to B. The house edge is not included here;
     * this is the amount that changed hands between the two accounts.
     *
     * This report is intentionally loaded in memory: it is an admin-only
     * monitoring view over aggregated sessions, not a high-traffic endpoint.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function pairs(?int $editionId = null): Collection
    {
        $lowId = 'LEAST(sessions.enrollment_id, sessions.challenger_enrollment_id)';
        $highId = 'GREATEST(sessions.enrollment_id, sessions.challenger_enrollment_id)';

        $rows = DB::table('arcade_sessions as sessions')
            ->join('arcade_games as games', 'games.id', '=', 'sessions.arcade_game_id')
            ->whereNotNull('sessions.challenger_enrollment_id')
            ->whereColumn('sessions.enrollment_id', '!=', 'sessions.challenger_enrollment_id')
            ->where('games.is_pvp', true)
            ->whereIn('sessions.outcome', ['win', 'loss'])
            ->whereNotNull('sessions.resolved_at')
            ->when($editionId !== null, fn ($query) => $query->where('games.edition_id', $editionId))
            ->selectRaw("{$lowId} as low_enrollment_id")
            ->selectRaw("{$highId} as high_enrollment_id")
            ->selectRaw('COUNT(*) as matches')
            ->selectRaw('SUM(sessions.wager) as total_wagered')
            ->selectRaw(
                "SUM(CASE WHEN ({$lowId} = sessions.enrollment_id AND sessions.outcome = 'win') ".
                "OR ({$lowId} = sessions.challenger_enrollment_id AND sessions.outcome = 'loss') ".
                'THEN 1 ELSE 0 END) as low_wins'
            )
            ->selectRaw(
                "SUM(CASE WHEN ({$highId} = sessions.enrollment_id AND sessions.outcome = 'win') ".
                "OR ({$highId} = sessions.challenger_enrollment_id AND sessions.outcome = 'loss') ".
                'THEN 1 ELSE 0 END) as high_wins'
            )
            ->selectRaw(
                "SUM(CASE WHEN ({$lowId} = sessions.enrollment_id AND sessions.outcome = 'loss') ".
                "OR ({$lowId} = sessions.challenger_enrollment_id AND sessions.outcome = 'win') ".
                'THEN sessions.wager ELSE 0 END) as low_losses'
            )
            ->selectRaw(
                "SUM(CASE WHEN ({$highId} = sessions.enrollment_id AND sessions.outcome = 'loss') ".
                "OR ({$highId} = sessions.challenger_enrollment_id AND sessions.outcome = 'win') ".
                'THEN sessions.wager ELSE 0 END) as high_losses'
            )
            ->selectRaw('MIN(sessions.resolved_at) as first_match_at')
            ->selectRaw('MAX(sessions.resolved_at) as last_match_at')
            ->groupByRaw("{$lowId}, {$highId}")
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $enrollmentIds = $rows
            ->flatMap(fn ($row) => [(int) $row->low_enrollment_id, (int) $row->high_enrollment_id])
            ->unique()
            ->values();

        $enrollments = Enrollment::query()
            ->with(['participant.user:id,name,email'])
            ->whereIn('id', $enrollmentIds)
            ->get()
            ->keyBy('id');

        $reviews = ArcadeNetFlowReview::query()
            ->with('reviewer:id,name')
            ->whereIn('low_enrollment_id', $rows->pluck('low_enrollment_id')->unique())
            ->get()
            ->keyBy(fn (ArcadeNetFlowReview $review) => $this->pairKey(
                $review->low_enrollment_id,
                $review->high_enrollment_id,
            ));

        return $rows->map(function ($row) use ($enrollments, $reviews) {
            $lowEnrollmentId = (int) $row->low_enrollment_id;
            $highEnrollmentId = (int) $row->high_enrollment_id;
            $matches = (int) $row->matches;
            $lowWins = (int) $row->low_wins;
            $highWins = (int) $row->high_wins;
            $lowLosses = (int) $row->low_losses;
            $highLosses = (int) $row->high_losses;
            $netFlow = abs($lowLosses - $highLosses);
            $dominantWins = max($lowWins, $highWins);
            $dominant = $lowWins === $highWins
                ? null
                : ($lowWins > $highWins ? 'low' : 'high');
            $review = $reviews->get($this->pairKey($lowEnrollmentId, $highEnrollmentId));
            $hasNewActivity = $review !== null && $matches > $review->reviewed_match_count;
            $reviewState = $review === null || $hasNewActivity || $review->status === ArcadeNetFlowReview::STATUS_OPEN
                ? self::REVIEW_UNREVIEWED
                : $review->status;

            return [
                'id' => $this->pairKey($lowEnrollmentId, $highEnrollmentId),
                'pair_key' => $this->pairKey($lowEnrollmentId, $highEnrollmentId),
                'low' => $this->accountData($enrollments->get($lowEnrollmentId), $lowEnrollmentId),
                'high' => $this->accountData($enrollments->get($highEnrollmentId), $highEnrollmentId),
                'matches' => $matches,
                'low_wins' => $lowWins,
                'high_wins' => $highWins,
                'total_wagered' => (int) $row->total_wagered,
                'low_losses' => $lowLosses,
                'high_losses' => $highLosses,
                'net_flow' => $netFlow,
                'flow_direction' => $this->flowDirection($lowLosses, $highLosses),
                'dominant' => $dominant,
                'dominant_wins' => $dominantWins,
                'dominant_share' => $matches > 0 ? round($dominantWins / $matches, 4) : 0.0,
                'risk_level' => $this->riskLevel($matches, $dominantWins, $netFlow),
                'risk_reasons' => $this->riskReasons($matches, $dominantWins, $netFlow),
                'first_match_at' => $row->first_match_at,
                'last_match_at' => $row->last_match_at,
                'review' => $review === null ? null : [
                    'status' => $review->status,
                    'note' => $review->note,
                    'reviewed_by' => $review->reviewer?->name,
                    'reviewed_at' => $review->reviewed_at?->toIso8601String(),
                    'reviewed_match_count' => (int) $review->reviewed_match_count,
                    'has_new_activity' => $hasNewActivity,
                ],
                'review_state' => $reviewState,
            ];
        });
    }

    public static function pairKey(int $lowEnrollmentId, int $highEnrollmentId): string
    {
        return min($lowEnrollmentId, $highEnrollmentId).'-'.max($lowEnrollmentId, $highEnrollmentId);
    }

    /**
     * @return array<string, mixed>
     */
    private function accountData(?Enrollment $enrollment, int $enrollmentId): array
    {
        $user = $enrollment?->participant?->user;

        return [
            'enrollment_id' => $enrollmentId,
            'participant_id' => $enrollment?->participant_id,
            'user_id' => $user?->id,
            'name' => $user?->name ?? 'Conta desconhecida',
            'email' => $user?->email,
        ];
    }

    private function flowDirection(int $lowLosses, int $highLosses): string
    {
        if ($lowLosses === $highLosses) {
            return 'balanced';
        }

        return $lowLosses > $highLosses ? 'low_to_high' : 'high_to_low';
    }

    private function riskLevel(int $matches, int $dominantWins, int $netFlow): string
    {
        $winShare = $matches > 0 ? $dominantWins / $matches : 0.0;

        if (
            $matches >= self::HIGH_MIN_MATCHES
            && $winShare >= self::HIGH_MIN_WIN_SHARE
            && $netFlow >= self::HIGH_MIN_NET_FLOW
        ) {
            return self::RISK_HIGH;
        }

        if (
            $matches >= self::MEDIUM_MIN_MATCHES
            && $winShare >= self::MEDIUM_MIN_WIN_SHARE
            && $netFlow >= self::MEDIUM_MIN_NET_FLOW
        ) {
            return self::RISK_MEDIUM;
        }

        return self::RISK_LOW;
    }

    /**
     * @return array<int, string>
     */
    private function riskReasons(int $matches, int $dominantWins, int $netFlow): array
    {
        $reasons = [];
        $winShare = $matches > 0 ? $dominantWins / $matches : 0.0;

        if ($matches >= self::MEDIUM_MIN_MATCHES && $winShare >= self::MEDIUM_MIN_WIN_SHARE) {
            $reasons[] = 'one_sided';
        }

        if ($netFlow >= self::MEDIUM_MIN_NET_FLOW) {
            $reasons[] = 'high_volume';
        }

        if ($matches >= self::HIGH_MIN_MATCHES) {
            $reasons[] = 'repeated_pair';
        }

        return $reasons;
    }
}
