<script setup lang="ts">
import type ArcadeGame from "@/Types/Arcade";

interface Props {
    game: ArcadeGame;
}

defineProps<Props>();
</script>

<template>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div
            class="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3"
        >
            <span
                class="text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
            >
                Aposta
            </span>
            <p class="mt-0.5 text-sm font-semibold text-white">
                {{ game.min_wager }}–{{ game.max_wager }}
                <span class="text-[11px] font-normal text-neutral-500">
                    pts
                </span>
            </p>
        </div>

        <div
            class="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3"
        >
            <span
                class="text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
            >
                Limite diário
            </span>
            <p
                v-if="game.daily_win_cap"
                class="mt-0.5 text-sm font-semibold text-white"
            >
                {{ game.daily_win_cap }}
                <span class="text-[11px] font-normal text-neutral-500">
                    pts
                </span>
            </p>
            <p v-else class="mt-0.5 text-sm font-semibold text-white">
                Sem limite
            </p>
        </div>

        <div
            class="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3"
        >
            <span
                class="text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
            >
                Apostas/dia
            </span>
            <p
                v-if="game.daily_bet_limit"
                class="mt-0.5 text-sm font-semibold text-white"
            >
                {{ game.daily_bet_limit }}
            </p>
            <p v-else class="mt-0.5 text-sm font-semibold text-white">
                Sem limite
            </p>
        </div>
    </div>
</template>
