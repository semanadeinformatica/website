<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import { X, AlertCircle } from "@lucide/vue";
import axios from "axios";

interface Props {
    game: ArcadeGame;
    points?: number;
    balance?: number;
}

const props = withDefaults(defineProps<Props>(), {
    points: 0,
    balance: undefined,
});

const emit = defineEmits<{
    (e: "close"): void;
}>();

const currentPropBalance = computed(() => props.balance ?? props.points ?? 0);
const localBalance = ref<number>(currentPropBalance.value);

watch(
    () => [props.points, props.balance],
    () => {
        localBalance.value = currentPropBalance.value;
    },
);

// Payout Config extraction with sensible fallbacks
const underThreshold = computed(
    () => (props.game.payout_config?.under_threshold as number) ?? 48,
);
const overThreshold = computed(
    () => (props.game.payout_config?.over_threshold as number) ?? 52,
);
const exactNumber = computed(
    () => (props.game.payout_config?.exact_number as number) ?? 50,
);
const underMultiplier = computed(
    () => (props.game.payout_config?.under_multiplier as number) ?? 2.0,
);
const overMultiplier = computed(
    () => (props.game.payout_config?.over_multiplier as number) ?? 2.0,
);
const exactMultiplier = computed(
    () => (props.game.payout_config?.exact_multiplier as number) ?? 20.0,
);

// Game state
const wager = ref<number>(
    Math.min(
        Math.max(props.game.min_wager, 20),
        props.game.max_wager,
        localBalance.value > 0 ? localBalance.value : props.game.max_wager,
    ),
);
const isPlaying = ref(false);
const selectedBet = ref<"under" | "over" | "exact" | null>(null);
const displayNumber = ref<number>(exactNumber.value);
const errorMessage = ref<string | null>(null);

const lastGameResult = ref<{
    result: number;
    won: boolean;
    payout: number;
    multiplier: number;
    wager: number;
} | null>(null);

// Dynamic color according to current number's range (Ruby < 48, Gold = 50, Roxo > 52)
const diceBoxColorClass = computed(() => {
    const val = displayNumber.value;

    if (val < underThreshold.value) {
        // Ruby Range (< 48)
        return "border-[#9a0036]/70 bg-[#630022]/35 text-[#d6336c] shadow-[0_0_30px_rgba(214,51,108,0.35)]";
    }

    if (val === exactNumber.value) {
        // Gold / Amber Jackpot (= 50)
        return "border-amber-400/80 bg-amber-500/25 text-amber-300 shadow-[0_0_35px_rgba(245,158,11,0.45)]";
    }

    if (val > overThreshold.value) {
        // Roxo Range (> 52)
        return "border-[#6a00ff]/70 bg-[#4300a3]/35 text-[#9346ff] shadow-[0_0_30px_rgba(147,70,255,0.35)]";
    }

    // Gap numbers (48, 49, 51, 52)
    return "border-white/20 bg-white/[0.06] text-white shadow-md";
});

// Validate if user can place a bet
const canPlay = computed(() => {
    return (
        !isPlaying.value &&
        wager.value >= props.game.min_wager &&
        wager.value <= props.game.max_wager &&
        wager.value <= localBalance.value
    );
});

async function play(betType: "under" | "over" | "exact") {
    if (!canPlay.value) return;

    isPlaying.value = true;
    selectedBet.value = betType;
    errorMessage.value = null;

    const currentWager = wager.value;

    // Start rapid number shuffle animation (random numbers 1-100 every 45ms)
    const animInterval = setInterval(() => {
        displayNumber.value = Math.floor(Math.random() * 100) + 1;
    }, 45);

    try {
        const response = await axios.post(route("arcade.high-low.play"), {
            wager: currentWager,
            bet_type: betType,
        });

        const data = response.data; // { result, won, payout, multiplier, balance }

        // Let the roll animation run for at least ~800ms for anticipation
        await new Promise((resolve) => setTimeout(resolve, 800));
        clearInterval(animInterval);

        displayNumber.value = data.result;
        lastGameResult.value = {
            ...data,
            wager: currentWager,
        };

        if (typeof data.balance === "number") {
            localBalance.value = data.balance;
            router.reload({ only: ["points"] });
        }
    } catch (err: unknown) {
        clearInterval(animInterval);
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value =
                "Ocorreu um erro ao processar a aposta. Tenta novamente.";
        }
    } finally {
        isPlaying.value = false;
    }
}
</script>

<template>
    <div
        class="relative flex max-h-[88vh] flex-col overflow-y-auto rounded-3xl border border-white/10 bg-neutral-950 p-4 shadow-2xl backdrop-blur-xl sm:p-6"
    >
        <!-- Modal Header -->
        <div
            class="flex items-center justify-between border-b border-white/10 pb-4"
        >
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="relative flex aspect-square h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/15 bg-white/[0.08] p-1.5 shadow-md"
                >
                    <img
                        src="/images/arcade/HighLow-Icon.png"
                        :alt="game.name"
                        class="aspect-square h-full w-full object-contain brightness-110 contrast-125 drop-shadow-[0_0_10px_rgba(147,70,255,0.6)] saturate-150"
                    />
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-bold tracking-tight text-white">
                        {{ game.name }}
                    </h3>
                    <p class="text-xs text-neutral-400">
                        Adivinha se o número sorteado (1–100) fica Abaixo, Acima
                        ou Exato
                    </p>
                </div>
            </div>

            <button
                type="button"
                :disabled="isPlaying"
                class="rounded-lg p-1.5 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white disabled:pointer-events-none disabled:opacity-40"
                @click="emit('close')"
            >
                <X :size="18" />
            </button>
        </div>

        <!-- Main Content -->
        <div class="space-y-5 pt-4">
            <!-- Wager Input -->
            <WagerInput
                v-model="wager"
                :min="game.min_wager"
                :max="game.max_wager"
                :balance="localBalance"
                :disabled="isPlaying"
            />

            <!-- Error Banner -->
            <div
                v-if="errorMessage"
                class="flex items-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-xs text-red-400"
            >
                <AlertCircle :size="16" class="shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <!-- Digital Roll Stage (Clean, seamless) -->
            <div
                class="relative flex flex-col items-center justify-center py-2 text-center"
            >
                <!-- Roll Status Pill -->
                <div class="relative z-10 mb-2">
                    <span
                        v-if="isPlaying"
                        class="inline-flex animate-pulse items-center gap-1.5 rounded-full border border-[#6a00ff]/50 bg-[#4300a3]/30 px-3 py-1 font-mono text-xs font-semibold text-[#9346ff] shadow-sm"
                    >
                        <span
                            class="h-1.5 w-1.5 animate-ping rounded-full bg-[#9346ff]"
                        />
                        A sortear número (1–100)...
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-3 py-1 font-mono text-xs font-medium text-neutral-400"
                    >
                        Faz a tua aposta e lança o dado
                    </span>
                </div>

                <!-- Number Display Counter (Changes Colors According to Range) -->
                <div
                    class="relative z-10 my-1 flex items-center justify-center"
                >
                    <div
                        :class="[
                            'flex h-24 w-24 items-center justify-center rounded-3xl border font-mono text-5xl font-black tracking-tight shadow-2xl transition-all duration-150 select-none sm:h-28 sm:w-28 sm:text-6xl',
                            isPlaying ? 'scale-105' : '',
                            diceBoxColorClass,
                        ]"
                    >
                        {{ displayNumber }}
                    </div>
                </div>

                <!-- Bottom Stage Area: Ruler (Idle/Rolling) OR Win/Loss Banner Over It (Result) -->
                <div
                    class="relative z-10 mt-4 flex h-12 w-full max-w-xs items-center justify-center sm:max-w-sm"
                >
                    <!-- Inline Victory (SINF Secondary Roxo Over Ruler) -->
                    <div
                        v-if="!isPlaying && lastGameResult?.won"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#6a00ff]/50 bg-[#4300a3]/50 px-3.5 py-2 text-xs shadow-[0_0_20px_rgba(106,0,255,0.25)] backdrop-blur-sm sm:text-sm"
                    >
                        <span class="font-bold text-[#9346ff]"
                            >Previsão correta!</span
                        >
                        <span class="text-neutral-300">Ganhaste</span>
                        <strong class="font-mono font-bold text-white"
                            >+{{ lastGameResult.payout }}</strong
                        >
                        <img
                            class="inline h-4 w-4 object-contain"
                            alt="SINFrão"
                            title="SINFrão"
                            src="/images/sinf-2026-sm.svg"
                        />
                    </div>

                    <!-- Inline Loss (Dark Red Over Ruler) -->
                    <div
                        v-else-if="
                            !isPlaying && lastGameResult && !lastGameResult.won
                        "
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-500/40 bg-red-950/50 px-3.5 py-2 text-xs shadow-md backdrop-blur-sm sm:text-sm"
                    >
                        <span class="font-bold text-red-400"
                            >Não foi desta!</span
                        >
                        <span class="text-neutral-300">Perdeste</span>
                        <strong class="font-mono font-bold text-white">{{
                            lastGameResult.wager
                        }}</strong>
                        <img
                            class="inline h-4 w-4 object-contain"
                            alt="SINFrão"
                            title="SINFrão"
                            src="/images/sinf-2026-sm.svg"
                        />
                    </div>

                    <!-- Ruler (Shown When Idle or Rolling) -->
                    <div v-else class="w-full">
                        <!-- Continuous Track with Faded Ends and Soft Faded Gold in the Middle -->
                        <div
                            class="relative flex h-2 w-full items-center overflow-hidden rounded-full bg-white/[0.04] [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]"
                        >
                            <!-- Segment 1: Ruby Zone -->
                            <div
                                class="h-full bg-gradient-to-r from-transparent via-[#9a0036] to-[#d6336c]"
                                :style="{ width: `${underThreshold - 1}%` }"
                            />

                            <!-- Segment 2: Faded Gold Middle (Soft Blend for exactNumber) -->
                            <div
                                class="relative flex h-full items-center justify-center bg-gradient-to-r from-transparent via-amber-400/50 to-transparent"
                                :style="{
                                    width: `${overThreshold - underThreshold + 1}%`,
                                }"
                            >
                                <div
                                    class="h-full w-1.5 rounded-full bg-amber-400 shadow-[0_0_8px_#fbbf24]"
                                    title="50"
                                />
                            </div>

                            <!-- Segment 3: Roxo Zone -->
                            <div
                                class="h-full bg-gradient-to-r from-[#6a00ff] via-[#9346ff] to-transparent"
                                :style="{ width: `${100 - overThreshold}%` }"
                            />
                        </div>

                        <!-- Clean Sub-Labels without < > or = -->
                        <div
                            class="mt-1.5 flex items-center justify-between px-1 font-mono text-[10px] text-neutral-400"
                        >
                            <span>1</span>
                            <span class="font-semibold text-[#d6336c]">
                                {{ underThreshold }}
                            </span>
                            <span class="font-bold text-amber-300">
                                {{ exactNumber }}
                            </span>
                            <span class="font-semibold text-[#9346ff]">
                                {{ overThreshold }}
                            </span>
                            <span>100</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1-Click Bet Action Buttons Side-by-Side (SINF Tri-Color) -->
            <div class="pt-1">
                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                    <!-- Under Button (SINF Ruby) -->
                    <button
                        type="button"
                        :disabled="!canPlay"
                        class="group relative flex flex-col items-center justify-center gap-1.5 rounded-2xl border border-[#9a0036]/50 bg-[#630022]/25 p-3.5 text-center shadow-[0_0_15px_rgba(154,0,54,0.15)] transition-all duration-200 hover:border-[#d6336c]/80 hover:bg-[#9a0036]/35 active:scale-95 disabled:pointer-events-none disabled:opacity-40"
                        @click="play('under')"
                    >
                        <span class="text-sm font-bold text-white">
                            Abaixo de {{ underThreshold }}
                        </span>
                        <span
                            class="rounded-full border border-[#d6336c]/50 bg-[#630022]/60 px-2.5 py-0.5 font-mono text-xs font-bold text-[#d6336c]"
                        >
                            ×{{ underMultiplier.toFixed(1) }}
                        </span>
                    </button>

                    <!-- Exact Button (SINF Gold / Amber Jackpot) -->
                    <button
                        type="button"
                        :disabled="!canPlay"
                        class="group relative flex flex-col items-center justify-center gap-1.5 rounded-2xl border border-amber-500/50 bg-amber-500/15 p-3.5 text-center shadow-[0_0_20px_rgba(245,158,11,0.2)] transition-all duration-200 hover:border-amber-400 hover:bg-amber-500/25 active:scale-95 disabled:pointer-events-none disabled:opacity-40"
                        @click="play('exact')"
                    >
                        <span class="text-sm font-bold text-amber-300">
                            Exatamente {{ exactNumber }}
                        </span>
                        <span
                            class="rounded-full border border-amber-500/60 bg-amber-950/80 px-2.5 py-0.5 font-mono text-xs font-black text-amber-300 shadow-sm"
                        >
                            ×{{ exactMultiplier.toFixed(1) }}
                        </span>
                    </button>

                    <!-- Over Button (SINF Secondary Roxo) -->
                    <button
                        type="button"
                        :disabled="!canPlay"
                        class="group relative flex flex-col items-center justify-center gap-1.5 rounded-2xl border border-[#6a00ff]/50 bg-[#4300a3]/25 p-3.5 text-center shadow-[0_0_15px_rgba(106,0,255,0.15)] transition-all duration-200 hover:border-[#6a00ff]/80 hover:bg-[#4300a3]/40 active:scale-95 disabled:pointer-events-none disabled:opacity-40"
                        @click="play('over')"
                    >
                        <span class="text-sm font-bold text-white">
                            Acima de {{ overThreshold }}
                        </span>
                        <span
                            class="rounded-full border border-[#6a00ff]/60 bg-[#4300a3]/60 px-2.5 py-0.5 font-mono text-xs font-bold text-[#9346ff]"
                        >
                            ×{{ overMultiplier.toFixed(1) }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
