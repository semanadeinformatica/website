<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import GameHeader from "@/Components/Arcade/GameHeader.vue";
import GameResult from "@/Components/Arcade/GameResult.vue";
import InfoBox from "@/Components/UI/InfoBox.vue";
import Modal from "@/Components/UI/Modal.vue";
import axios from "axios";

interface Props {
    game: ArcadeGame;
    points?: number;
    balance?: number;
    show: boolean;
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

// Payout config extraction with numeric coercion and sensible fallbacks (preserving 0)
const underThreshold = computed(() =>
    Number(props.game.payout_config?.under_threshold ?? 48),
);
const overThreshold = computed(() =>
    Number(props.game.payout_config?.over_threshold ?? 52),
);
const exactNumber = computed(() =>
    Number(props.game.payout_config?.exact_number ?? 50),
);
const underMultiplier = computed(() =>
    Number(props.game.payout_config?.under_multiplier ?? 2.0),
);
const overMultiplier = computed(() =>
    Number(props.game.payout_config?.over_multiplier ?? 2.0),
);
const exactMultiplier = computed(() =>
    Number(props.game.payout_config?.exact_multiplier ?? 20.0),
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
const displayNumber = ref<number>(exactNumber.value);
const errorMessage = ref<string | null>(null);

const lastGameResult = ref<{
    result: number;
    won: boolean;
    payout: number;
    multiplier: number;
    wager: number;
} | null>(null);

let animInterval: ReturnType<typeof setInterval> | null = null;

function stopAnimation() {
    if (animInterval) {
        clearInterval(animInterval);
        animInterval = null;
    }
}

onBeforeUnmount(stopAnimation);

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
    errorMessage.value = null;
    lastGameResult.value = null;

    const currentWager = wager.value;

    // Start rapid number shuffle animation (random numbers 1-100 every 45ms)
    stopAnimation();
    animInterval = setInterval(() => {
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
        stopAnimation();

        displayNumber.value = data.result;
        lastGameResult.value = {
            ...data,
            wager: currentWager,
        };

        if (typeof data.balance === "number") {
            localBalance.value = data.balance;
            router.reload({ only: ["points", "enrollment_points"] });
        }
    } catch (err: unknown) {
        stopAnimation();
        displayNumber.value = exactNumber.value;
        lastGameResult.value = null;
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

function resetGame() {
    lastGameResult.value = null;
    displayNumber.value = exactNumber.value;
    errorMessage.value = null;
}
</script>

<template>
    <Modal
        :show="show"
        max-width="lg"
        :close-disabled="isPlaying"
        :click-to-close="!isPlaying"
        :esc-to-close="!isPlaying"
        @close="emit('close')"
    >
        <template #header>
            <GameHeader
                :game="game"
                :show-limits="!isPlaying && !lastGameResult"
                description="Adivinha se o número sorteado fica abaixo, acima ou exatamente no valor."
            />
        </template>

        <div class="space-y-5">
            <WagerInput
                v-if="!lastGameResult"
                v-model="wager"
                :min="game.min_wager"
                :max="game.max_wager"
                :balance="localBalance"
                :disabled="isPlaying"
            />

            <InfoBox v-if="errorMessage" variant="danger">
                {{ errorMessage }}
            </InfoBox>

            <!-- Number stage -->
            <div class="flex flex-col items-center py-1">
                <div
                    class="flex h-24 w-24 items-center justify-center rounded-3xl border border-white/10 bg-white/[0.03] text-5xl font-bold tracking-tight text-white select-none sm:h-28 sm:w-28 sm:text-6xl"
                >
                    {{ displayNumber }}
                </div>

                <div class="mt-4 w-full max-w-sm">
                    <div
                        class="h-1.5 w-full overflow-hidden rounded-full bg-white/10"
                    >
                        <div
                            class="h-full rounded-full bg-white/35 transition-all duration-150"
                            :style="{ width: `${displayNumber}%` }"
                        />
                    </div>
                    <div
                        class="mt-1.5 flex justify-between text-[11px] text-neutral-600"
                    >
                        <span>1</span>
                        <span>100</span>
                    </div>
                </div>
            </div>

            <GameResult
                v-if="lastGameResult && !isPlaying"
                :outcome="lastGameResult.won ? 'win' : 'loss'"
                :wager="lastGameResult.wager"
                :payout="lastGameResult.payout"
                :multiplier="lastGameResult.multiplier"
                @play-again="resetGame"
            />

            <div v-else class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                <button
                    type="button"
                    :disabled="!canPlay"
                    class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-center transition-colors hover:border-white/20 hover:bg-white/[0.06] active:scale-[0.98] disabled:pointer-events-none disabled:opacity-40"
                    @click="play('under')"
                >
                    <span class="block text-sm font-semibold text-white">
                        Abaixo de {{ underThreshold }}
                    </span>
                    <span class="mt-1 block text-xs text-neutral-500">
                        ×{{ underMultiplier }}
                    </span>
                </button>

                <button
                    type="button"
                    :disabled="!canPlay"
                    class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-center transition-colors hover:border-white/20 hover:bg-white/[0.06] active:scale-[0.98] disabled:pointer-events-none disabled:opacity-40"
                    @click="play('exact')"
                >
                    <span class="block text-sm font-semibold text-white">
                        Exatamente {{ exactNumber }}
                    </span>
                    <span class="mt-1 block text-xs text-neutral-500">
                        ×{{ exactMultiplier }}
                    </span>
                </button>

                <button
                    type="button"
                    :disabled="!canPlay"
                    class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-center transition-colors hover:border-white/20 hover:bg-white/[0.06] active:scale-[0.98] disabled:pointer-events-none disabled:opacity-40"
                    @click="play('over')"
                >
                    <span class="block text-sm font-semibold text-white">
                        Acima de {{ overThreshold }}
                    </span>
                    <span class="mt-1 block text-xs text-neutral-500">
                        ×{{ overMultiplier }}
                    </span>
                </button>
            </div>
        </div>
    </Modal>
</template>
