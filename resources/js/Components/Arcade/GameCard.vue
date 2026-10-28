<script setup lang="ts">
import { computed } from "vue";
import type ArcadeGame from "@/Types/Arcade";
import Card from "@/Components/UI/Card.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { Coins, Dices, Grid3x3, Key, Zap, Gamepad2, Swords } from "@lucide/vue";

interface Props {
    game: ArcadeGame;
    points?: number;
    isEnrolled: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: "play"): void;
}>();

const iconComponent = computed(() => {
    switch (props.game.slug) {
        case "coin-flip":
            return Coins;
        case "high-low":
            return Dices;
        case "bug-sweeper":
            return Grid3x3;
        case "mastermind":
            return Key;
        case "binary-blitz":
            return Zap;
        default:
            return Gamepad2;
    }
});
</script>

<template>
    <Card
        as="div"
        :interactive="game.is_active"
        padding="p-5 sm:p-6"
        :class="[
            'relative overflow-hidden transition-all duration-300',
            !game.is_active ? 'opacity-60 grayscale' : '',
        ]"
    >
        <!-- Inactive Overlay -->
        <div
            v-if="!game.is_active"
            class="absolute inset-0 z-20 flex items-center justify-center bg-black/60 backdrop-blur-[2px]"
        >
            <span
                class="rounded-full border border-white/10 bg-neutral-900/90 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-neutral-400 shadow-md"
            >
                Indisponível
            </span>
        </div>

        <div class="flex h-full flex-col justify-between space-y-5">
            <!-- Header: Icon & Badges -->
            <div class="flex items-start justify-between">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-amber-400 shadow-inner"
                >
                    <component :is="iconComponent" :size="24" />
                </div>

                <div class="flex items-center gap-2">
                    <span
                        v-if="game.is_pvp"
                        class="inline-flex items-center gap-1 rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-xs font-semibold text-amber-400"
                    >
                        <Swords :size="12" />
                        PvP
                    </span>
                    <span
                        v-else
                        class="rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-medium text-neutral-400"
                    >
                        Solo
                    </span>
                </div>
            </div>

            <!-- Game Info -->
            <div class="space-y-1.5">
                <h3 class="text-lg font-bold text-white tracking-tight">
                    {{ game.name }}
                </h3>
                <div class="flex flex-col gap-1 text-xs text-neutral-400">
                    <span class="flex items-center gap-1.5">
                        <span class="text-neutral-500">Aposta:</span>
                        <span class="font-mono text-neutral-300">
                            {{ game.min_wager }}–{{ game.max_wager }} pts
                        </span>
                    </span>
                    <span
                        v-if="game.daily_win_cap"
                        class="flex items-center gap-1.5"
                    >
                        <span class="text-neutral-500">Limite diário:</span>
                        <span class="font-mono text-amber-400/90">
                            {{ game.daily_win_cap }} pts
                        </span>
                    </span>
                </div>
            </div>

            <!-- Action Button -->
            <div class="relative pt-2">
                <div
                    v-if="!isEnrolled"
                    class="group/tooltip relative w-full"
                >
                    <PrimaryButton
                        disabled
                        class="w-full justify-center opacity-50 cursor-not-allowed"
                    >
                        Jogar
                    </PrimaryButton>
                    <div
                        class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-neutral-900 px-2.5 py-1 text-[11px] text-neutral-300 opacity-0 shadow-lg transition-opacity duration-200 group-hover/tooltip:opacity-100 ring-1 ring-white/10"
                    >
                        Inscreve-te para jogar
                    </div>
                </div>

                <PrimaryButton
                    v-else
                    :disabled="!game.is_active"
                    class="w-full justify-center"
                    @click="emit('play')"
                >
                    Jogar
                </PrimaryButton>
            </div>
        </div>
    </Card>
</template>
