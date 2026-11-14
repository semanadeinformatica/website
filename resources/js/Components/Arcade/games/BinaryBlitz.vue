<script setup lang="ts">
import { computed, onUnmounted, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import ResultOverlay from "@/Components/Arcade/ResultOverlay.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { AlertCircle, CheckCircle2, X, XCircle, Zap } from "@lucide/vue";
import axios from "axios";

interface Props {
    game: ArcadeGame;
    points?: number;
    balance?: number;
}

interface Prompt {
    prompt: string;
}

interface BinaryBlitzResult {
    correct: number;
    total: number;
    payout: number;
    outcome: "win" | "partial" | "loss";
    feedback: boolean[];
    wager: number;
}

type State = "idle" | "starting" | "playing" | "submitting" | "result";

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

const questionCount = computed(
    () => (props.game.payout_config?.question_count as number) ?? 3,
);
const timeLimit = computed(
    () => (props.game.payout_config?.time_limit_seconds as number) ?? 12,
);

const state = ref<State>("idle");
const wager = ref<number>(
    Math.min(
        Math.max(props.game.min_wager, 20),
        props.game.max_wager,
        localBalance.value > 0 ? localBalance.value : props.game.max_wager,
    ),
);
const activeWager = ref(0);
const sessionId = ref<number | null>(null);
const prompts = ref<Prompt[]>([]);
const answers = ref<number[]>([]);
const deadline = ref<string | null>(null);
const remainingSeconds = ref(0);
const feedback = ref<boolean[]>([]);
const lastResult = ref<BinaryBlitzResult | null>(null);
const showResultOverlay = ref(false);
const errorMessage = ref<string | null>(null);
const isStarting = computed(() => state.value === "starting");
const isSubmitting = computed(() => state.value === "submitting");
const isLocked = computed(
    () =>
        state.value === "starting" ||
        state.value === "playing" ||
        state.value === "submitting",
);

let timer: ReturnType<typeof setInterval> | null = null;
let autoSubmitted = false;

const canStart = computed(
    () =>
        state.value === "idle" &&
        wager.value >= props.game.min_wager &&
        wager.value <= props.game.max_wager &&
        wager.value <= localBalance.value,
);

const canSubmit = computed(
    () => state.value === "playing" && !isSubmitting.value,
);

function stopTimer() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

function reloadBalance() {
    router.reload({ only: ["points", "enrollment_points"] });
}

function tick() {
    if (!deadline.value) {
        remainingSeconds.value = 0;
        return;
    }

    const diff = Date.parse(deadline.value) - Date.now();
    remainingSeconds.value = Math.max(0, Math.ceil(diff / 1000));

    if (diff <= 0) {
        stopTimer();
        void submitGame(true);
    }
}

function startTimer() {
    stopTimer();
    tick();

    if (state.value === "playing" && remainingSeconds.value > 0) {
        timer = setInterval(tick, 250);
    }
}

async function startGame() {
    if (!canStart.value) return;

    state.value = "starting";
    errorMessage.value = null;

    try {
        const response = await axios.post(route("arcade.binary-blitz.start"), {
            wager: wager.value,
        });
        const data = response.data;

        sessionId.value = data.session_id;
        prompts.value = data.prompts ?? [];
        answers.value = prompts.value.map(() => 0);
        deadline.value = data.deadline;
        activeWager.value = wager.value;
        remainingSeconds.value = timeLimit.value;
        feedback.value = [];
        lastResult.value = null;
        showResultOverlay.value = false;
        autoSubmitted = false;
        errorMessage.value = null;
        state.value = "playing";

        if (typeof data.points === "number") {
            localBalance.value = data.points;
        }

        reloadBalance();
        startTimer();
    } catch (error: unknown) {
        state.value = "idle";

        if (axios.isAxiosError(error) && error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = "Não foi possível iniciar o jogo.";
        }
    }
}

async function submitGame(auto = false) {
    if (state.value !== "playing" || !sessionId.value) return;
    if (auto && autoSubmitted) return;

    if (auto) {
        autoSubmitted = true;
    }

    state.value = "submitting";
    stopTimer();

    const payload = answers.value.map((answer) => {
        const value = Number(answer);
        return Number.isFinite(value) ? Math.trunc(value) : 0;
    });

    try {
        const response = await axios.post(
            route("arcade.binary-blitz.submit", {
                session: sessionId.value,
            }),
            { answers: payload },
        );
        const data = response.data;

        feedback.value = data.feedback ?? [];
        lastResult.value = {
            correct: data.correct,
            total: data.total,
            payout: data.payout,
            outcome: data.outcome,
            feedback: feedback.value,
            wager: activeWager.value,
        };

        if (typeof data.balance === "number") {
            localBalance.value = data.balance;
        }

        state.value = "result";
        showResultOverlay.value = true;
        reloadBalance();
    } catch (error: unknown) {
        const message =
            axios.isAxiosError(error) && error.response?.data?.message
                ? error.response.data.message
                : "Não foi possível submeter as respostas.";

        errorMessage.value = message;
        autoSubmitted = false;
        reloadBalance();

        if (
            message.toLowerCase().includes("tempo") ||
            remainingSeconds.value <= 0
        ) {
            state.value = "idle";
        } else {
            state.value = "playing";
            startTimer();
        }
    }
}

function resetGame() {
    stopTimer();
    state.value = "idle";
    sessionId.value = null;
    prompts.value = [];
    answers.value = [];
    deadline.value = null;
    remainingSeconds.value = 0;
    feedback.value = [];
    lastResult.value = null;
    showResultOverlay.value = false;
    errorMessage.value = null;
    autoSubmitted = false;
    reloadBalance();
}

function closeGame() {
    stopTimer();
    emit("close");
}

onUnmounted(() => {
    stopTimer();
});
</script>

<template>
    <div
        class="relative flex max-h-[90vh] flex-col overflow-y-auto rounded-3xl border border-white/10 bg-neutral-950 p-4 shadow-2xl backdrop-blur-xl sm:p-6"
    >
        <div
            class="flex items-center justify-between border-b border-white/10 pb-4"
        >
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="relative flex aspect-square h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/15 bg-white/[0.08] p-1.5 text-amber-400 shadow-md"
                >
                    <Zap :size="24" />
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-bold tracking-tight text-white">
                        {{ game.name }}
                    </h3>
                    <p class="text-xs text-neutral-400">
                        Resolve {{ questionCount }} desafios em
                        {{ timeLimit }} segundos
                    </p>
                </div>
            </div>

            <button
                type="button"
                :disabled="isLocked"
                class="rounded-lg p-1.5 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white disabled:pointer-events-none disabled:opacity-40"
                aria-label="Fechar"
                @click="closeGame"
            >
                <X :size="18" />
            </button>
        </div>

        <div class="space-y-4 pt-4">
            <div
                v-if="errorMessage"
                class="flex items-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-xs text-red-400"
            >
                <AlertCircle :size="16" class="shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <div
                v-if="state === 'idle' || state === 'starting'"
                class="space-y-5"
            >
                <WagerInput
                    v-model="wager"
                    :min="game.min_wager"
                    :max="game.max_wager"
                    :balance="localBalance"
                    :disabled="isStarting"
                />

                <PrimaryButton
                    class="w-full justify-center py-3 text-sm font-bold shadow-lg"
                    :disabled="!canStart"
                    @click="startGame"
                >
                    <span v-if="isStarting">A preparar desafios...</span>
                    <span v-else>Iniciar</span>
                </PrimaryButton>
            </div>

            <div v-else class="space-y-4">
                <div
                    class="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2"
                >
                    <span class="font-mono text-xs text-neutral-400">
                        {{ lastResult ? "Resultado" : "Tempo restante" }}
                    </span>
                    <span
                        v-if="!lastResult"
                        class="font-mono text-sm font-bold"
                        :class="
                            remainingSeconds <= 3
                                ? 'text-red-400'
                                : 'text-white'
                        "
                    >
                        {{ remainingSeconds }}s
                    </span>
                    <span
                        v-else
                        class="font-mono text-xs font-bold text-amber-300"
                    >
                        {{ lastResult.correct }}/{{ lastResult.total }} corretas
                    </span>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="(question, index) in prompts"
                        :key="index"
                        class="rounded-2xl border border-white/10 bg-neutral-900/60 p-3"
                    >
                        <svg
                            class="h-10 w-full"
                            viewBox="0 0 420 40"
                            role="img"
                            :aria-label="question.prompt"
                        >
                            <text
                                x="8"
                                y="27"
                                fill="white"
                                font-family="monospace"
                                font-size="18"
                                style="user-select: none"
                            >
                                {{ question.prompt }}
                            </text>
                        </svg>

                        <div class="flex items-center gap-2">
                            <input
                                v-model.number="answers[index]"
                                type="number"
                                min="0"
                                :disabled="state !== 'playing'"
                                :aria-label="`Resposta ${index + 1}`"
                                class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 font-mono text-sm text-white outline-none focus:border-amber-400/50 disabled:opacity-60"
                            />

                            <CheckCircle2
                                v-if="
                                    state === 'result' &&
                                    feedback[index] === true
                                "
                                :size="20"
                                class="shrink-0 text-emerald-400"
                            />
                            <XCircle
                                v-else-if="
                                    state === 'result' &&
                                    feedback[index] === false
                                "
                                :size="20"
                                class="shrink-0 text-red-400"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="state === 'playing' || state === 'submitting'"
                    class="space-y-3"
                >
                    <PrimaryButton
                        class="w-full justify-center py-2.5 text-sm font-bold shadow-lg"
                        :disabled="!canSubmit"
                        @click="submitGame()"
                    >
                        <span v-if="isSubmitting">A verificar...</span>
                        <span v-else>Submeter</span>
                    </PrimaryButton>
                    <p
                        class="text-center font-mono text-[10px] text-neutral-500"
                    >
                        A submissão é automática quando o tempo termina
                    </p>
                </div>

                <PrimaryButton
                    v-if="state === 'result'"
                    class="w-full justify-center py-2.5 text-sm font-bold shadow-lg"
                    @click="resetGame"
                >
                    Jogar novamente
                </PrimaryButton>
            </div>
        </div>

        <ResultOverlay
            :show="showResultOverlay"
            :outcome="lastResult?.outcome ?? null"
            :wager="lastResult?.wager ?? 0"
            :payout="lastResult?.payout ?? 0"
            @close="showResultOverlay = false"
            @play-again="resetGame"
        />
    </div>
</template>
