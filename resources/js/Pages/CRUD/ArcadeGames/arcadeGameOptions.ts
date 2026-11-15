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
