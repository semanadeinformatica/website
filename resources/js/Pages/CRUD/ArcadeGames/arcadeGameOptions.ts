export interface ArcadeGameSlugOption {
    value: string;
    label: string;
}

export const arcadeGameSlugs: ArcadeGameSlugOption[] = [
    { value: "coin-flip", label: "Coin Flip" },
    { value: "high-low", label: "High Low" },
    { value: "bug-sweeper", label: "Bug Sweeper" },
    { value: "mastermind", label: "Mastermind" },
    { value: "binary-blitz", label: "Binary Blitz" },
];

export interface PayoutConfigExample {
    slug: string;
    config: string;
}

export const payoutConfigExamples: PayoutConfigExample[] = [
    {
        slug: "coin-flip",
        config: '{"house_edge_pct": 10}',
    },
    {
        slug: "high-low",
        config: '{"under_threshold": 48, "over_threshold": 52, "exact_number": 50, "under_multiplier": 2.0, "over_multiplier": 2.0, "exact_multiplier": 20.0}',
    },
    {
        slug: "bug-sweeper",
        config: '{"bomb_count": 3, "click_multipliers": [1.2, 1.5, 2.0, 3.0, 5.0]}',
    },
    {
        slug: "mastermind",
        config: '{"max_attempts": 5, "attempt_multipliers": {"1": 3.0, "2": 1.5, "3": 1.1, "4": 1.0, "5": 1.0}}',
    },
    {
        slug: "binary-blitz",
        config: '{"question_count": 10, "time_limit_seconds": 60, "win_multiplier": 2.5, "partial_multiplier": 1.2}',
    },
];
