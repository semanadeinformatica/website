export default interface ArcadeGame {
    id: number;
    edition_id: number;
    slug:
        | 'coin-flip'
        | 'high-low'
        | 'bug-sweeper'
        | 'mastermind'
        | 'binary-blitz';
    name: string;
    is_active: boolean;
    is_pvp: boolean;
    min_wager: number;
    max_wager: number;
    daily_win_cap: number | null;
    payout_config: Record<string, unknown>;
}

export interface ArcadeSession {
    id: number;
    enrollment_id: number;
    challenger_enrollment_id: number | null;
    wager: number;
    outcome: 'pending' | 'matched' | 'win' | 'loss' | 'cashout' | 'expired';
    payout: number;
    started_at: string;
    resolved_at: string | null;
}
