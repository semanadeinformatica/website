<script setup lang="ts">
import { computed } from "vue";
import type CompetitionTeam from "@/Types/CompetitionTeam";
import type { CompetitionPrizes } from "@/Types/Competition";

interface Props {
    mode?: "winners" | "prizes";
    prizes?: CompetitionPrizes[];
    leaderboard?: CompetitionTeam[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: "winners",
    prizes: () => [],
    leaderboard: () => [],
});

const teamFirst = computed(() => props.leaderboard[0]);
const teamSecond = computed(() => props.leaderboard[1]);
const teamThird = computed(() => props.leaderboard[2]);

const prizeFirst = computed(
    () => props.prizes.find((p) => p.place === 1) ?? props.prizes[0],
);
const prizeSecond = computed(
    () => props.prizes.find((p) => p.place === 2) ?? props.prizes[1],
);
const prizeThird = computed(
    () => props.prizes.find((p) => p.place === 3) ?? props.prizes[2],
);
</script>

<template>
    <section class="relative mx-auto flex w-full flex-col items-center py-4">
        <div
            class="relative flex w-full max-w-4xl items-end justify-center gap-3 px-2 sm:gap-6 sm:px-6"
        >
            <div class="flex flex-1 flex-col items-center">
                <div
                    v-if="mode === 'prizes'"
                    class="group relative mb-3 flex flex-col items-center"
                >
                    <div
                        class="relative flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border border-slate-300/40 bg-neutral-900 p-1.5 shadow-[0_0_20px_rgba(203,213,225,0.15)] ring-2 ring-slate-300/30 backdrop-blur-md transition-transform duration-300 group-hover:scale-105 sm:h-28 sm:w-28 sm:p-2 md:h-32 md:w-32"
                    >
                        <img
                            v-if="prizeSecond?.prize_image_url"
                            :src="prizeSecond.prize_image_url"
                            alt="2º Prémio"
                            class="h-full w-full rounded-xl object-contain p-1"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-slate-400"
                        >
                            <svg
                                class="h-10 w-10 opacity-50"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="mb-3 text-center">
                    <p
                        v-if="mode === 'winners' && teamSecond"
                        class="max-w-25 truncate text-xs font-bold text-white sm:max-w-35 sm:text-sm"
                    >
                        {{ teamSecond.name }}
                    </p>
                    <p
                        v-if="mode === 'winners' && teamSecond"
                        class="text-[11px] font-medium text-slate-300/80 sm:text-xs"
                    >
                        {{ teamSecond.points }} pts
                    </p>
                    <p
                        v-else-if="mode === 'prizes' && prizeSecond"
                        class="text-xs font-medium text-neutral-300 sm:text-sm"
                    >
                        2º Prémio
                    </p>
                </div>

                <div
                    class="flex h-28 w-full flex-col items-center justify-center rounded-t-2xl border-x border-t border-slate-300/20 bg-linear-to-b from-slate-400/10 via-black/50 to-black/80 p-2 shadow-[inset_0_1px_1px_rgba(255,255,255,0.08)] backdrop-blur-md sm:h-36 md:h-44"
                >
                    <span
                        class="font-mono text-3xl font-black text-slate-300/30 sm:text-5xl md:text-6xl"
                    >
                        2
                    </span>
                </div>
            </div>

            <div class="flex flex-1 flex-col items-center">
                <div
                    v-if="mode === 'prizes'"
                    class="group relative mb-3 flex flex-col items-center"
                >
                    <div
                        class="relative flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-amber-400/50 bg-neutral-900 p-1.5 shadow-[0_0_30px_rgba(251,191,36,0.25)] ring-2 ring-amber-400/50 backdrop-blur-md transition-transform duration-300 group-hover:scale-105 sm:h-36 sm:w-36 sm:p-2.5 md:h-40 md:w-40"
                    >
                        <img
                            v-if="prizeFirst?.prize_image_url"
                            :src="prizeFirst.prize_image_url"
                            alt="1º Prémio"
                            class="h-full w-full rounded-xl object-contain p-1"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-amber-400"
                        >
                            <svg
                                class="h-12 w-12 opacity-60"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="mb-3 text-center">
                    <p
                        v-if="mode === 'winners' && teamFirst"
                        class="max-w-30 truncate text-sm font-bold text-white sm:max-w-40 sm:text-base"
                    >
                        {{ teamFirst.name }}
                    </p>
                    <p
                        v-if="mode === 'winners' && teamFirst"
                        class="text-xs font-semibold text-amber-300 sm:text-sm"
                    >
                        {{ teamFirst.points }} pts
                    </p>
                    <p
                        v-else-if="mode === 'prizes' && prizeFirst"
                        class="text-xs font-semibold text-amber-300 sm:text-sm"
                    >
                        1º Prémio
                    </p>
                </div>

                <div
                    class="flex h-36 w-full flex-col items-center justify-center rounded-t-2xl border-x border-t border-amber-400/30 bg-linear-to-b from-amber-500/15 via-black/50 to-black/80 p-2 shadow-[inset_0_1px_1px_rgba(255,255,255,0.12)] backdrop-blur-md sm:h-48 md:h-56"
                >
                    <span
                        class="font-mono text-4xl font-black text-amber-400/35 sm:text-6xl md:text-7xl"
                    >
                        1
                    </span>
                </div>
            </div>

            <div class="flex flex-1 flex-col items-center">
                <div
                    v-if="mode === 'prizes'"
                    class="group relative mb-3 flex flex-col items-center"
                >
                    <div
                        class="relative flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border border-amber-700/40 bg-neutral-900 p-1.5 shadow-[0_0_20px_rgba(180,83,9,0.15)] ring-2 ring-amber-700/30 backdrop-blur-md transition-transform duration-300 group-hover:scale-105 sm:h-28 sm:w-28 sm:p-2 md:h-32 md:w-32"
                    >
                        <img
                            v-if="prizeThird?.prize_image_url"
                            :src="prizeThird.prize_image_url"
                            alt="3º Prémio"
                            class="h-full w-full rounded-xl object-contain p-1"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-amber-700"
                        >
                            <svg
                                class="h-10 w-10 opacity-50"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="mb-3 text-center">
                    <p
                        v-if="mode === 'winners' && teamThird"
                        class="max-w-25 truncate text-xs font-bold text-white sm:max-w-35 sm:text-sm"
                    >
                        {{ teamThird.name }}
                    </p>
                    <p
                        v-if="mode === 'winners' && teamThird"
                        class="text-[11px] font-medium text-amber-400/80 sm:text-xs"
                    >
                        {{ teamThird.points }} pts
                    </p>
                    <p
                        v-else-if="mode === 'prizes' && prizeThird"
                        class="text-xs font-medium text-neutral-300 sm:text-sm"
                    >
                        3º Prémio
                    </p>
                </div>

                <div
                    class="flex h-24 w-full flex-col items-center justify-center rounded-t-2xl border-x border-t border-amber-700/20 bg-linear-to-b from-amber-800/10 via-black/50 to-black/80 p-2 shadow-[inset_0_1px_1px_rgba(255,255,255,0.06)] backdrop-blur-md sm:h-32 md:h-36"
                >
                    <span
                        class="font-mono text-3xl font-black text-amber-700/30 sm:text-5xl md:text-6xl"
                    >
                        3
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
