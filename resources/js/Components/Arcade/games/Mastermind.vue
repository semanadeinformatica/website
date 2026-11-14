<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import ResultOverlay from "@/Components/Arcade/ResultOverlay.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { Key, X, AlertCircle, ChevronUp, ChevronDown } from "@lucide/vue";
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

// Game Configuration
const maxAttempts = computed<number>(
    () => (props.game.payout_config?.max_attempts as number) ?? 5,
);

const attemptMultipliers = computed<Record<string, number>>(() => {
    return (
        (props.game.payout_config?.attempt_multipliers as Record<
            string,
            number
        >) ?? {}
    );
});

// Game state
const gameState = ref<"idle" | "playing" | "resolved">("idle");
const wager = ref<number>(
    Math.min(
        Math.max(props.game.min_wager, 10),
        props.game.max_wager,
        localBalance.value > 0 ? localBalance.value : props.game.max_wager,
    ),
);
const activeWager = ref<number>(wager.value);
const sessionId = ref<number | null>(null);
const isActionLoading = ref(false);
const errorMessage = ref<string | null>(null);

// Attempts history: Array of { guess: string, exact: number, misplaced: number, feedback: Array<"exact" | "misplaced" | "wrong"> }
interface AttemptRecord {
    guess: string;
    exact: number;
    misplaced: number;
    feedback: Array<"exact" | "misplaced" | "wrong">;
}
const attempts = ref<AttemptRecord[]>([]);

// Active Tumbler rotor state (4 digits: 0-9)
const tumblers = ref<[number, number, number, number]>([0, 0, 0, 0]);
const focusedIndex = ref<number>(0);

// Result state
const lastResult = ref<{
    outcome: "win" | "loss";
    payout: number;
    multiplier: number;
    code: string | null;
} | null>(null);

// Computed attempt trackers
const currentAttemptNumber = computed(() => attempts.value.length + 1);

const currentMultiplier = computed(() => {
    const key = String(Math.min(currentAttemptNumber.value, maxAttempts.value));
    return attemptMultipliers.value[key] ?? 1.0;
});

const currentPotentialPayout = computed(() => {
    return Math.floor(activeWager.value * currentMultiplier.value);
});

const canStart = computed(() => {
    return (
        gameState.value === "idle" &&
        !isActionLoading.value &&
        wager.value >= props.game.min_wager &&
        wager.value <= props.game.max_wager &&
        wager.value <= localBalance.value
    );
});

// Tumbler spin methods
function incrementTumbler(index: number) {
    if (gameState.value !== "playing" || isActionLoading.value) return;
    focusedIndex.value = index;
    tumblers.value[index] = (tumblers.value[index] + 1) % 10;
}

function decrementTumbler(index: number) {
    if (gameState.value !== "playing" || isActionLoading.value) return;
    focusedIndex.value = index;
    tumblers.value[index] = (tumblers.value[index] + 9) % 10;
}

function setTumblerFocus(index: number) {
    if (gameState.value !== "playing") return;
    focusedIndex.value = index;
}

// Physical keyboard listener
function handleKeyDown(e: KeyboardEvent) {
    if (gameState.value !== "playing" || isActionLoading.value) return;

    // Digits 0-9
    if (e.key >= "0" && e.key <= "9") {
        e.preventDefault();
        tumblers.value[focusedIndex.value] = parseInt(e.key, 10);
        if (focusedIndex.value < 3) {
            focusedIndex.value++;
        }
        return;
    }

    // Navigation and controls
    if (e.key === "ArrowLeft") {
        e.preventDefault();
        focusedIndex.value = Math.max(0, focusedIndex.value - 1);
    } else if (e.key === "ArrowRight") {
        e.preventDefault();
        focusedIndex.value = Math.min(3, focusedIndex.value + 1);
    } else if (e.key === "ArrowUp") {
        e.preventDefault();
        incrementTumbler(focusedIndex.value);
    } else if (e.key === "ArrowDown") {
        e.preventDefault();
        decrementTumbler(focusedIndex.value);
    } else if (e.key === "Backspace") {
        e.preventDefault();
        tumblers.value[focusedIndex.value] = 0;
        focusedIndex.value = Math.max(0, focusedIndex.value - 1);
    } else if (e.key === "Enter") {
        e.preventDefault();
        submitGuess();
    }
}

onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeyDown);
});

// API Actions: Start Game
async function startGame() {
    if (!canStart.value) return;

    isActionLoading.value = true;
    errorMessage.value = null;
    activeWager.value = wager.value;

    try {
        const response = await axios.post(route("arcade.mastermind.start"), {
            wager: activeWager.value,
        });

        const data = response.data;
        sessionId.value = data.session_id;
        attempts.value = [];
        tumblers.value = [0, 0, 0, 0];
        focusedIndex.value = 0;
        lastResult.value = null;
        gameState.value = "playing";

        if (typeof data.balance === "number") {
            localBalance.value = data.balance;
            router.reload({ only: ["points", "enrollment_points"] });
        }
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Erro ao iniciar o jogo. Tenta novamente.";
        }
    } finally {
        isActionLoading.value = false;
    }
}

// API Actions: Submit Guess
async function submitGuess() {
    if (
        gameState.value !== "playing" ||
        isActionLoading.value ||
        !sessionId.value
    )
        return;

    isActionLoading.value = true;
    errorMessage.value = null;

    const guessString = tumblers.value.join("");

    try {
        const response = await axios.post(
            route("arcade.mastermind.guess", { session: sessionId.value }),
            { guess: guessString },
        );

        const data = response.data;

        // Record attempt in history with individual digit feedbacks
        attempts.value.push({
            guess: guessString,
            exact: data.exact,
            misplaced: data.misplaced,
            feedback: data.feedback ?? [],
        });

        if (data.outcome === "win" || data.outcome === "loss") {
            gameState.value = "resolved";
            lastResult.value = {
                outcome: data.outcome,
                payout: data.payout ?? 0,
                multiplier: data.multiplier ?? 0,
                code: data.code ?? null,
            };

            if (typeof data.balance === "number") {
                localBalance.value = data.balance;
                router.reload({ only: ["points", "enrollment_points"] });
            }
        } else {
            // Reset focus to the first column for the next attempt
            focusedIndex.value = 0;
        }
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Erro ao submeter o palpite. Tenta novamente.";
        }
    } finally {
        isActionLoading.value = false;
    }
}

// Reset to play again
function resetGame() {
    gameState.value = "idle";
    sessionId.value = null;
    attempts.value = [];
    tumblers.value = [0, 0, 0, 0];
    focusedIndex.value = 0;
    lastResult.value = null;
    errorMessage.value = null;
}
</script>

<template>
    <div
        class="relative flex max-h-[90vh] flex-col overflow-y-auto rounded-3xl border border-white/10 bg-neutral-950 p-4 shadow-2xl backdrop-blur-xl sm:p-6"
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
                        src="/images/arcade/Mastermind-Icon.png"
                        :alt="game.name"
                        class="aspect-square h-full w-full object-contain brightness-110 contrast-125 drop-shadow-[0_0_10px_rgba(147,70,255,0.6)] saturate-150"
                    />
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-bold tracking-tight text-white">
                        {{ game.name }}
                    </h3>
                    <p class="text-xs text-neutral-400">
                        Desencripta o código secreto de 4 dígitos em até
                        {{ maxAttempts }} tentativas
                    </p>
                </div>
            </div>

            <button
                type="button"
                :disabled="isActionLoading || gameState === 'playing'"
                class="rounded-lg p-1.5 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white disabled:pointer-events-none disabled:opacity-40"
                @click="emit('close')"
            >
                <X :size="18" />
            </button>
        </div>

        <!-- Main Content Area -->
        <div class="space-y-4 pt-4">
            <!-- Error Banner -->
            <div
                v-if="errorMessage"
                class="flex items-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-xs text-red-400"
            >
                <AlertCircle :size="16" class="shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <!-- ===================== IDLE STATE ===================== -->
            <div v-if="gameState === 'idle'" class="space-y-5">
                <!-- Wager Input -->
                <WagerInput
                    v-model="wager"
                    :min="game.min_wager"
                    :max="game.max_wager"
                    :balance="localBalance"
                    :disabled="isActionLoading"
                />

                <!-- Color Key / Hint Rules (Responsive Cards with generous spacing) -->
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <div
                        class="flex items-center gap-2.5 rounded-xl border border-white/10 bg-neutral-900/60 p-2.5 transition-colors hover:border-[#6a00ff]/30 sm:flex-col sm:p-3 sm:text-center"
                    >
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#9346ff] bg-[#4300a3]/50 font-mono text-sm font-black text-white shadow-[0_0_12px_rgba(147,70,255,0.4)] sm:h-9 sm:w-9 sm:text-base"
                            >0</span
                        >
                        <span class="text-xs font-semibold text-neutral-200"
                            >Posição Certa</span
                        >
                    </div>

                    <div
                        class="flex items-center gap-2.5 rounded-xl border border-white/10 bg-neutral-900/60 p-2.5 transition-colors hover:border-neutral-500/40 sm:flex-col sm:p-3 sm:text-center"
                    >
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-500 bg-neutral-700/60 font-mono text-sm font-black text-neutral-200 sm:h-9 sm:w-9 sm:text-base"
                            >0</span
                        >
                        <span class="text-xs font-semibold text-neutral-200"
                            >Posição Errada</span
                        >
                    </div>

                    <div
                        class="flex items-center gap-2.5 rounded-xl border border-white/10 bg-neutral-900/60 p-2.5 transition-colors hover:border-[#9a0036]/40 sm:flex-col sm:p-3 sm:text-center"
                    >
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#9a0036]/70 bg-[#630022]/40 font-mono text-sm font-black text-[#d6336c] sm:h-9 sm:w-9 sm:text-base"
                            >0</span
                        >
                        <span class="text-xs font-semibold text-neutral-200"
                            >Não Existe</span
                        >
                    </div>
                </div>

                <!-- Start Action Button -->
                <PrimaryButton
                    class="w-full justify-center py-3 text-sm font-bold shadow-lg"
                    :disabled="!canStart"
                    @click="startGame"
                >
                    <span v-if="isActionLoading">A inicializar cofre...</span>
                    <span
                        v-else
                        class="inline-flex items-center justify-center gap-1.5"
                    >
                        <span>Desencriptar</span>
                        <span>{{ wager }}</span>
                        <img
                            src="/images/sinf-2026-sm.svg"
                            class="inline h-3.5 w-3.5 object-contain"
                            alt="SINFrão"
                            title="SINFrão"
                        />
                    </span>
                </PrimaryButton>
            </div>

            <!-- ===================== PLAYING OR RESOLVED STATE ===================== -->
            <div v-else class="space-y-4">
                <!-- Attempt Status & Live Multiplier Badge Header -->
                <div
                    class="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2"
                >
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs text-neutral-400"
                            >Tentativa:</span
                        >
                        <span class="font-mono text-xs font-bold text-white">
                            {{ Math.min(currentAttemptNumber, maxAttempts) }} /
                            {{ maxAttempts }}
                        </span>
                    </div>

                    <div
                        v-if="gameState === 'playing'"
                        class="flex items-center gap-1.5 rounded-lg border border-[#6a00ff]/40 bg-[#4300a3]/25 px-2.5 py-1 text-xs shadow-sm"
                    >
                        <span class="font-mono font-bold text-amber-300">
                            {{ currentPotentialPayout }}
                        </span>
                        <img
                            src="/images/sinf-2026-sm.svg"
                            class="inline h-3.5 w-3.5 object-contain"
                            alt="SINFrão"
                            title="SINFrão"
                        />
                    </div>
                    <div
                        v-else
                        class="font-mono text-xs font-bold"
                        :class="
                            lastResult?.outcome === 'win'
                                ? 'text-amber-300'
                                : 'text-neutral-400'
                        "
                    >
                        Jogo Terminado
                    </div>
                </div>

                <!-- History Matrix (5 Attempts Rows with Colored Digit Boxes, No Side Dots) -->
                <div
                    class="space-y-2 rounded-2xl border border-white/10 bg-neutral-900/60 p-3 sm:p-4"
                >
                    <div
                        v-for="rowIndex in maxAttempts"
                        :key="rowIndex"
                        class="flex items-center gap-2.5 rounded-xl border px-2.5 transition-all duration-200 sm:gap-3.5 sm:px-3.5"
                        :class="[
                            attempts[rowIndex - 1]
                                ? 'border-white/15 bg-white/[0.03] py-2'
                                : rowIndex === currentAttemptNumber &&
                                    gameState === 'playing'
                                  ? 'border-[#6a00ff]/50 bg-[#4300a3]/20 py-2 shadow-[0_0_15px_rgba(106,0,255,0.15)] sm:py-2.5'
                                  : 'border-white/5 bg-transparent py-2 opacity-40',
                        ]"
                    >
                        <!-- Row Multiplier Label (Fixed width, never shrinks, cleanly aligned) -->
                        <div
                            class="w-9 shrink-0 font-mono text-xs font-bold sm:w-11"
                            :class="
                                rowIndex === currentAttemptNumber &&
                                gameState === 'playing'
                                    ? 'text-[#9346ff]'
                                    : 'text-neutral-400'
                            "
                        >
                            {{ attemptMultipliers[String(rowIndex)] ?? 1.0 }}x
                        </div>

                        <!-- 4 Digit Boxes with Wordle SINF Color Coding (Aligned Left) -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <template v-if="attempts[rowIndex - 1]">
                                <div
                                    v-for="(char, cIdx) in attempts[
                                        rowIndex - 1
                                    ].guess.split('')"
                                    :key="cIdx"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border font-mono text-base font-black shadow-inner transition-all select-none sm:h-11 sm:w-11 sm:text-lg"
                                    :class="[
                                        attempts[rowIndex - 1].feedback[
                                            cIdx
                                        ] === 'exact'
                                            ? 'border-[#9346ff] bg-[#4300a3]/50 text-white shadow-[0_0_12px_rgba(147,70,255,0.4)]'
                                            : attempts[rowIndex - 1].feedback[
                                                    cIdx
                                                ] === 'misplaced'
                                              ? 'border-neutral-500 bg-neutral-700/60 text-neutral-200'
                                              : 'border-[#9a0036]/70 bg-[#630022]/40 text-[#d6336c]',
                                    ]"
                                >
                                    {{ char }}
                                </div>
                            </template>
                            <template
                                v-else-if="
                                    rowIndex === currentAttemptNumber &&
                                    gameState === 'playing'
                                "
                            >
                                <div
                                    v-for="(digit, dIdx) in tumblers"
                                    :key="dIdx"
                                    class="flex flex-col items-center"
                                >
                                    <!-- Up Arrow Button -->
                                    <button
                                        type="button"
                                        :disabled="isActionLoading"
                                        class="flex h-5 w-10 items-center justify-center rounded-t-lg border border-b-0 border-white/10 bg-white/5 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white active:bg-white/20 disabled:opacity-40 sm:h-6 sm:w-11"
                                        @click.stop="incrementTumbler(dIdx)"
                                    >
                                        <ChevronUp :size="14" />
                                    </button>

                                    <!-- Digit Box -->
                                    <div
                                        class="flex h-10 w-10 cursor-pointer items-center justify-center border font-mono text-base font-black transition-all select-none sm:h-11 sm:w-11 sm:text-lg"
                                        :class="
                                            focusedIndex === dIdx
                                                ? 'border-[#9346ff] bg-[#4300a3]/40 text-white shadow-[0_0_12px_rgba(147,70,255,0.4)]'
                                                : 'border-white/20 bg-neutral-950/80 text-neutral-200'
                                        "
                                        @click="setTumblerFocus(dIdx)"
                                    >
                                        {{ digit }}
                                    </div>

                                    <!-- Down Arrow Button -->
                                    <button
                                        type="button"
                                        :disabled="isActionLoading"
                                        class="flex h-5 w-10 items-center justify-center rounded-b-lg border border-t-0 border-white/10 bg-white/5 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white active:bg-white/20 disabled:opacity-40 sm:h-6 sm:w-11"
                                        @click.stop="decrementTumbler(dIdx)"
                                    >
                                        <ChevronDown :size="14" />
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <div
                                    v-for="emptySlot in 4"
                                    :key="emptySlot"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/5 bg-white/[0.01] font-mono text-base font-bold text-neutral-600 sm:h-11 sm:w-11 sm:text-lg"
                                >
                                    ·
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Submit Action (Playing State) -->
                <div v-if="gameState === 'playing'" class="space-y-3 pt-1">
                    <!-- Submit Guess Action Button -->
                    <PrimaryButton
                        class="w-full justify-center py-2.5 text-sm font-bold shadow-lg"
                        :disabled="isActionLoading"
                        @click="submitGuess"
                    >
                        <span v-if="isActionLoading">A verificar...</span>
                        <span v-else>Tentar</span>
                    </PrimaryButton>

                    <div
                        class="text-center font-mono text-[10px] text-neutral-500"
                    >
                        Podes usar as setas ▲/▼ ou digitar 0–9 diretamente no
                        teclado
                    </div>
                </div>
            </div>
        </div>

        <ResultOverlay
            :show="gameState === 'resolved'"
            :outcome="lastResult?.outcome ?? null"
            :wager="activeWager"
            :payout="lastResult?.payout ?? 0"
            :multiplier="lastResult?.multiplier"
            :code="lastResult?.code"
            @close="emit('close')"
            @play-again="resetGame"
        />
    </div>
</template>
