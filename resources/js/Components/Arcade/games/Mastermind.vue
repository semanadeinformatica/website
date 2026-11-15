<script setup lang="ts">
import { ref, computed, watch, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import GameHeader from "@/Components/Arcade/GameHeader.vue";
import GameResult from "@/Components/Arcade/GameResult.vue";
import InfoBox from "@/Components/UI/InfoBox.vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
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

// Game configuration
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

interface AttemptRecord {
    guess: string;
    exact: number;
    misplaced: number;
    feedback: Array<"exact" | "misplaced" | "wrong">;
}
const attempts = ref<AttemptRecord[]>([]);

// Active tumbler rotor state (4 digits: 0-9)
const tumblers = ref<[number, number, number, number]>([0, 0, 0, 0]);
const digitInputs = ref<HTMLInputElement[]>([]);

const lastResult = ref<{
    outcome: "win" | "loss";
    payout: number;
    multiplier: number;
    code: string | null;
} | null>(null);

const currentAttemptNumber = computed(() => attempts.value.length + 1);

const displayedAttemptNumber = computed(() =>
    gameState.value === "playing"
        ? Math.min(currentAttemptNumber.value, maxAttempts.value)
        : attempts.value.length,
);

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

const canClose = computed(
    () => !isActionLoading.value && gameState.value !== "playing",
);

function setDigitInput(el: unknown, index: number) {
    if (el instanceof HTMLInputElement) {
        digitInputs.value[index] = el;
    }
}

function focusDigit(index: number) {
    if (index < 0 || index > 3) return;
    nextTick(() => digitInputs.value[index]?.focus());
}

function selectInputText(event: FocusEvent) {
    (event.target as HTMLInputElement).select();
}

function incrementTumbler(index: number) {
    if (gameState.value !== "playing" || isActionLoading.value) return;
    tumblers.value[index] = (tumblers.value[index] + 1) % 10;
    focusDigit(index);
}

function decrementTumbler(index: number) {
    if (gameState.value !== "playing" || isActionLoading.value) return;
    tumblers.value[index] = (tumblers.value[index] + 9) % 10;
    focusDigit(index);
}

function handleDigitInput(index: number, event: Event) {
    if (gameState.value !== "playing" || isActionLoading.value) return;

    const input = event.target as HTMLInputElement;
    const raw = input.value.replace(/\D/g, "");
    const digit = raw ? parseInt(raw.slice(-1), 10) : 0;

    tumblers.value[index] = digit;
    input.value = String(digit);

    if (raw && index < 3) {
        focusDigit(index + 1);
    }
}

function handleDigitKeydown(index: number, event: KeyboardEvent) {
    switch (event.key) {
        case "ArrowLeft":
            event.preventDefault();
            focusDigit(index - 1);
            break;
        case "ArrowRight":
            event.preventDefault();
            focusDigit(index + 1);
            break;
        case "ArrowUp":
            event.preventDefault();
            incrementTumbler(index);
            break;
        case "ArrowDown":
            event.preventDefault();
            decrementTumbler(index);
            break;
        case "Backspace":
            event.preventDefault();
            tumblers.value[index] = 0;
            focusDigit(index > 0 ? index - 1 : 0);
            break;
        case "Enter":
            event.preventDefault();
            submitGuess();
            break;
    }
}

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
        if (gameState.value === "playing") {
            focusDigit(0);
        }
    }
}

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
        }
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Erro ao submeter o palpite. Tenta novamente.";
        }
    } finally {
        isActionLoading.value = false;
        if (gameState.value === "playing") {
            focusDigit(0);
        }
    }
}

function resetGame() {
    gameState.value = "idle";
    sessionId.value = null;
    attempts.value = [];
    tumblers.value = [0, 0, 0, 0];
    lastResult.value = null;
    errorMessage.value = null;
}
</script>

<template>
    <Modal
        :show="show"
        max-width="lg"
        :close-disabled="!canClose"
        :click-to-close="canClose"
        :esc-to-close="canClose"
        @close="emit('close')"
    >
        <template #header>
            <GameHeader
                :game="game"
                :show-limits="gameState === 'idle'"
                :description="`Desencripta o código secreto de 4 dígitos em até ${maxAttempts} tentativas.`"
            />
        </template>

        <div class="space-y-4">
            <InfoBox v-if="errorMessage" variant="danger">
                {{ errorMessage }}
            </InfoBox>

            <!-- Idle -->
            <div v-if="gameState === 'idle'" class="space-y-5">
                <InfoBox title="Como jogar">
                    <p>
                        Desencripta o código secreto de 4 dígitos. Cada
                        tentativa mostra o resultado de cada dígito:
                    </p>

                    <div class="mt-2.5 flex flex-wrap gap-x-4 gap-y-2">
                        <span
                            class="inline-flex items-center gap-1.5 text-[11px] font-medium text-neutral-300"
                        >
                            <span
                                class="border-sinf-secondary bg-sinf-secondary-dark/50 flex h-5 w-5 items-center justify-center rounded-md border text-[10px] font-semibold text-white"
                            >
                                0
                            </span>
                            Posição certa
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 text-[11px] font-medium text-neutral-300"
                        >
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-md border border-neutral-500 bg-neutral-700/60 text-[10px] font-semibold text-neutral-200"
                            >
                                0
                            </span>
                            Posição errada
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 text-[11px] font-medium text-neutral-300"
                        >
                            <span
                                class="border-sinf-primary/70 bg-sinf-primary-dark/40 text-sinf-primary-light flex h-5 w-5 items-center justify-center rounded-md border text-[10px] font-semibold"
                            >
                                0
                            </span>
                            Não existe
                        </span>
                    </div>
                </InfoBox>

                <WagerInput
                    v-model="wager"
                    :min="game.min_wager"
                    :max="game.max_wager"
                    :balance="localBalance"
                    :disabled="isActionLoading"
                />

                <PrimaryButton
                    class="w-full justify-center"
                    :disabled="!canStart"
                    @click="startGame"
                >
                    {{ isActionLoading ? "A iniciar..." : "Jogar" }}
                </PrimaryButton>
            </div>

            <!-- Playing / resolved -->
            <div v-else class="space-y-4">
                <div
                    class="flex items-center justify-between rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-3"
                >
                    <span class="text-xs text-neutral-400">
                        Tentativa
                        <strong class="ml-1 text-white">
                            {{ displayedAttemptNumber }} / {{ maxAttempts }}
                        </strong>
                    </span>

                    <span
                        v-if="gameState === 'playing'"
                        class="flex items-center gap-1.5 text-xs text-neutral-400"
                    >
                        Prémio potencial
                        <strong class="text-amber-300">
                            {{ currentPotentialPayout }}
                        </strong>
                        <img
                            src="/images/sinf-2026-sm.svg"
                            class="h-3.5 w-3.5 object-contain"
                            alt="SINFrão"
                            title="SINFrão"
                        />
                    </span>
                    <span v-else class="text-xs text-neutral-400">
                        Jogo terminado
                    </span>
                </div>

                <div class="space-y-2">
                    <div
                        v-for="rowIndex in maxAttempts"
                        :key="rowIndex"
                        class="flex items-center gap-2.5 rounded-xl border px-2.5 py-2 transition-all duration-200 sm:gap-3.5 sm:px-3.5"
                        :class="[
                            attempts[rowIndex - 1]
                                ? 'border-white/10 bg-white/[0.03]'
                                : rowIndex === currentAttemptNumber &&
                                    gameState === 'playing'
                                  ? 'border-sinf-secondary/50 bg-sinf-secondary/10'
                                  : 'border-white/5 bg-transparent opacity-40',
                        ]"
                    >
                        <div
                            class="w-9 shrink-0 text-center text-xs font-semibold sm:w-11"
                            :class="
                                rowIndex === currentAttemptNumber &&
                                gameState === 'playing'
                                    ? 'text-sinf-secondary-light'
                                    : 'text-neutral-400'
                            "
                        >
                            {{ attemptMultipliers[String(rowIndex)] ?? 1.0 }}x
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <template v-if="attempts[rowIndex - 1]">
                                <div
                                    v-for="(char, cIdx) in attempts[
                                        rowIndex - 1
                                    ].guess.split('')"
                                    :key="cIdx"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border text-base font-semibold transition-all select-none sm:h-11 sm:w-11 sm:text-lg"
                                    :class="[
                                        attempts[rowIndex - 1].feedback[
                                            cIdx
                                        ] === 'exact'
                                            ? 'border-sinf-secondary-light bg-sinf-secondary-dark/50 text-white'
                                            : attempts[rowIndex - 1].feedback[
                                                    cIdx
                                                ] === 'misplaced'
                                              ? 'border-neutral-500 bg-neutral-700/60 text-neutral-200'
                                              : 'border-sinf-primary/60 bg-sinf-primary-dark/40 text-sinf-primary-light',
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
                                <input
                                    v-for="(digit, dIdx) in tumblers"
                                    :key="dIdx"
                                    :ref="
                                        (el) => setDigitInput(el, Number(dIdx))
                                    "
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    :aria-label="`Dígito ${Number(dIdx) + 1}`"
                                    :value="digit"
                                    :disabled="isActionLoading"
                                    class="focus:border-sinf-secondary-light focus:bg-sinf-secondary-dark/40 h-10 w-10 rounded-xl border border-white/15 bg-neutral-950/80 text-center text-base font-semibold text-neutral-200 transition-colors select-none focus:text-white focus:outline-none disabled:opacity-50 sm:h-11 sm:w-11 sm:text-lg"
                                    @input="
                                        handleDigitInput(Number(dIdx), $event)
                                    "
                                    @keydown="
                                        handleDigitKeydown(Number(dIdx), $event)
                                    "
                                    @focus="selectInputText($event)"
                                />
                            </template>

                            <template v-else>
                                <div
                                    v-for="emptySlot in 4"
                                    :key="emptySlot"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/5 bg-white/[0.01] text-base font-semibold text-neutral-600 sm:h-11 sm:w-11 sm:text-lg"
                                >
                                    ·
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div v-if="gameState === 'playing'" class="space-y-2">
                    <PrimaryButton
                        class="w-full justify-center"
                        :disabled="isActionLoading"
                        @click="submitGuess"
                    >
                        {{ isActionLoading ? "A verificar..." : "Tentar" }}
                    </PrimaryButton>

                    <p class="text-center text-[11px] text-neutral-500">
                        Seleciona uma caixa, digita 0–9 e prime Enter
                    </p>
                </div>

                <GameResult
                    v-if="gameState === 'resolved'"
                    :outcome="lastResult?.outcome ?? null"
                    :wager="activeWager"
                    :payout="lastResult?.payout ?? 0"
                    :multiplier="lastResult?.multiplier"
                    :code="lastResult?.code"
                    @play-again="resetGame"
                />
            </div>
        </div>
    </Modal>
</template>
