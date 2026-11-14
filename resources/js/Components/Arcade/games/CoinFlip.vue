<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import Modal from "@/Components/UI/Modal.vue";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import ResultOverlay from "@/Components/Arcade/ResultOverlay.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import {
    AlertCircle,
    Clock,
    Coins,
    Loader2,
    RefreshCw,
    User as UserIcon,
    X,
} from "@lucide/vue";

interface Props {
    game: ArcadeGame;
    points: number;
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: "close"): void;
}>();

type State =
    "idle" | "starting" | "waiting" | "joining" | "flipping" | "result";

const state = ref<State>("idle");
const wager = ref(props.game.min_wager);
const sessionId = ref<number | null>(null);
const outcome = ref<"win" | "loss" | null>(null);
const payout = ref(0);
const errorMessage = ref<string | null>(null);

const currentBalance = ref(props.points);
watch(
    () => props.points,
    (val) => {
        currentBalance.value = val;
    },
);

let startRequestId = 0;
let pollInterval: ReturnType<typeof setInterval> | null = null;
let waitingTimer: ReturnType<typeof setInterval> | null = null;
const waitingSeconds = ref(0);
const canCancel = ref(false);
const isCancelling = ref(false);

const PENDING_SESSION_KEY = "sinf_arcade_coin_flip_session";

interface StoredSession {
    id: number;
    wager: number;
    startedAt: number;
}

function savePendingSession(id: number, bet: number) {
    try {
        const item: StoredSession = {
            id,
            wager: bet,
            startedAt: Date.now(),
        };
        sessionStorage.setItem(PENDING_SESSION_KEY, JSON.stringify(item));
    } catch {
        // ignore storage errors
    }
}

function getStoredPendingSession(): StoredSession | null {
    try {
        const raw = sessionStorage.getItem(PENDING_SESSION_KEY);
        if (!raw) return null;
        return JSON.parse(raw) as StoredSession;
    } catch {
        return null;
    }
}

function clearPendingSession() {
    try {
        sessionStorage.removeItem(PENDING_SESSION_KEY);
    } catch {
        // ignore
    }
}

interface LobbySession {
    id: number;
    enrollment_id: number;
    wager: number;
    started_at: string;
    enrollment?: {
        participant?: {
            user?: {
                id: number;
                name: string;
                profile_photo_url?: string;
            } | null;
        } | null;
    } | null;
}

const lobbySessions = ref<LobbySession[]>([]);
const isLoadingLobby = ref(false);
const joiningSessionId = ref<number | null>(null);

const isWagerActionInProgress = computed(
    () => state.value === "starting" || joiningSessionId.value !== null,
);

async function fetchLobby() {
    isLoadingLobby.value = true;
    try {
        const res = await axios.get(route("arcade.coin-flip.lobby"));
        const data = res.data;
        lobbySessions.value = Array.isArray(data) ? data : (data.data ?? []);
    } catch {
        // Silently handle lobby fetching failure
    } finally {
        isLoadingLobby.value = false;
    }
}

function stopPolling() {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

function stopWaitingTimer() {
    if (waitingTimer) {
        clearInterval(waitingTimer);
        waitingTimer = null;
    }
    waitingSeconds.value = 0;
    canCancel.value = false;
}

function startWaitingTimer(initialSeconds = 0) {
    stopWaitingTimer();
    waitingSeconds.value = initialSeconds;
    canCancel.value = initialSeconds >= 30;
    waitingTimer = setInterval(() => {
        waitingSeconds.value++;
        if (waitingSeconds.value >= 30) {
            canCancel.value = true;
        }
    }, 1000);
}

async function startGame() {
    if (isWagerActionInProgress.value) return;
    if (wager.value > currentBalance.value) {
        errorMessage.value = "Saldo insuficiente para esta aposta.";
        return;
    }

    const requestId = ++startRequestId;
    state.value = "starting";
    errorMessage.value = null;

    try {
        const res = await axios.post(route("arcade.coin-flip.start"), {
            wager: wager.value,
        });
        if (requestId !== startRequestId) {
            router.reload({ only: ["points", "enrollment_points"] });
            return;
        }
        const data = res.data;
        sessionId.value = data.session_id;
        currentBalance.value = Math.max(0, currentBalance.value - wager.value);
        savePendingSession(data.session_id, wager.value);
        state.value = "waiting";
        startWaitingTimer();
        startPolling();
    } catch (err: unknown) {
        if (requestId !== startRequestId) return;
        state.value = "idle";
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Ocorreu um erro ao criar o jogo.";
        }
    }
}

function startPolling() {
    stopPolling();
    let requestInFlight = false;

    pollInterval = setInterval(async () => {
        const polledSessionId = sessionId.value;
        if (!polledSessionId || requestInFlight) return;
        requestInFlight = true;

        try {
            const res = await axios.get(
                route("arcade.coin-flip.status", { session: polledSessionId }),
            );
            if (polledSessionId !== sessionId.value) return;

            const data = res.data;
            if (data.outcome && data.outcome !== "pending") {
                stopPolling();
                stopWaitingTimer();
                clearPendingSession();
                state.value = "flipping";

                setTimeout(() => {
                    if (polledSessionId !== sessionId.value) return;
                    outcome.value = data.outcome;
                    payout.value = data.payout;
                    if (data.outcome === "win") {
                        currentBalance.value += payout.value;
                    }
                    state.value = "result";
                    router.reload({ only: ["points", "enrollment_points"] });
                }, 1500); // wait for flip animation
            }
        } catch (err: unknown) {
            if (axios.isAxiosError(err) && err.response?.status === 404) {
                if (polledSessionId !== sessionId.value) return;
                stopPolling();
                stopWaitingTimer();
                clearPendingSession();
                state.value = "idle";
                sessionId.value = null;
                errorMessage.value = "A sessão já não se encontra disponível.";
            }
        } finally {
            requestInFlight = false;
        }
    }, 2000);
}

async function joinGame(session: LobbySession) {
    if (isWagerActionInProgress.value) return;
    if (session.wager > currentBalance.value) {
        errorMessage.value = "Saldo insuficiente para aderir a esta sala.";
        return;
    }

    joiningSessionId.value = session.id;
    errorMessage.value = null;

    try {
        const res = await axios.post(
            route("arcade.coin-flip.join", { session: session.id }),
            { wager: session.wager },
        );
        sessionId.value = session.id;
        wager.value = session.wager;
        currentBalance.value = Math.max(
            0,
            currentBalance.value - session.wager,
        );
        state.value = "flipping";

        const data = res.data;
        setTimeout(() => {
            outcome.value = data.outcome;
            payout.value =
                data.player_payout ??
                (data.outcome === "win" ? data.payout : 0);
            if (data.outcome === "win") {
                currentBalance.value += payout.value;
            }
            state.value = "result";
            router.reload({ only: ["points", "enrollment_points"] });
        }, 1500);
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Não foi possível aderir ao jogo.";
        }
        await fetchLobby();
    } finally {
        joiningSessionId.value = null;
    }
}

async function cancelGame() {
    if (!sessionId.value) return;
    isCancelling.value = true;
    errorMessage.value = null;

    try {
        await axios.post(
            route("arcade.coin-flip.cancel", { session: sessionId.value }),
        );
        stopPolling();
        stopWaitingTimer();
        clearPendingSession();
        sessionId.value = null;
        state.value = "idle";
        handleClose();
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Não foi possível cancelar o jogo.";
        }
    } finally {
        isCancelling.value = false;
    }
}

function handleClose() {
    startRequestId++;
    stopPolling();
    stopWaitingTimer();

    if (state.value !== "waiting") {
        clearPendingSession();
        state.value = "idle";
        sessionId.value = null;
        outcome.value = null;
        payout.value = 0;
    }

    errorMessage.value = null;
    emit("close");
    router.reload({ only: ["points", "enrollment_points"] });
}

function handlePlayAgain() {
    startRequestId++;
    stopPolling();
    stopWaitingTimer();
    clearPendingSession();
    sessionId.value = null;
    outcome.value = null;
    payout.value = 0;
    errorMessage.value = null;
    state.value = "idle";
    fetchLobby();
    router.reload({ only: ["points", "enrollment_points"] });
}

async function restoreOrResetSession() {
    const stored = getStoredPendingSession();
    if (stored) {
        sessionId.value = stored.id;
        wager.value = stored.wager;
        const elapsed = Math.max(
            0,
            Math.floor((Date.now() - stored.startedAt) / 1000),
        );
        state.value = "waiting";
        startWaitingTimer(elapsed);
        startPolling();

        try {
            const res = await axios.get(
                route("arcade.coin-flip.status", { session: stored.id }),
            );
            const data = res.data;
            if (data.outcome && data.outcome !== "pending") {
                stopPolling();
                stopWaitingTimer();
                clearPendingSession();
                state.value = "flipping";

                setTimeout(() => {
                    outcome.value = data.outcome;
                    payout.value = data.payout;
                    if (data.outcome === "win") {
                        currentBalance.value += payout.value;
                    }
                    state.value = "result";
                    router.reload({ only: ["points", "enrollment_points"] });
                }, 1500);
            }
        } catch (err: unknown) {
            if (axios.isAxiosError(err) && err.response?.status === 404) {
                stopPolling();
                stopWaitingTimer();
                clearPendingSession();
                state.value = "idle";
                sessionId.value = null;
            }
        }
    } else {
        if (state.value !== "waiting") {
            state.value = "idle";
            errorMessage.value = null;
            wager.value = props.game.min_wager;
        }
    }
    fetchLobby();
}

watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            restoreOrResetSession();
        } else {
            startRequestId++;
            stopPolling();
            stopWaitingTimer();
        }
    },
    { immediate: true },
);

onMounted(() => {
    fetchLobby();
});

onUnmounted(() => {
    startRequestId++;
    stopPolling();
    stopWaitingTimer();
});
</script>

<template>
    <Modal
        :show="show"
        :show-close-button="false"
        max-width="lg"
        @close="handleClose"
    >
        <div
            class="relative flex flex-col overflow-hidden rounded-3xl border border-white/10 bg-neutral-950 p-6 shadow-2xl backdrop-blur-xl"
        >
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between border-b border-white/10 pb-4"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400"
                    >
                        <Coins :size="20" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">
                            {{ game.name }}
                        </h3>
                        <span class="font-mono text-xs text-neutral-400">
                            {{ game.min_wager }}–{{ game.max_wager }} pts • PvP
                            em tempo real
                        </span>
                    </div>
                </div>
                <button
                    v-if="state !== 'flipping'"
                    type="button"
                    class="rounded-lg p-1.5 text-neutral-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Fechar"
                    @click="handleClose"
                >
                    <X :size="18" />
                </button>
            </div>

            <!-- Error Banner -->
            <div
                v-if="errorMessage"
                class="mt-4 flex items-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-xs text-red-400"
            >
                <AlertCircle :size="16" class="shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <!-- State: Flipping Animation (Plays for 1.5s) -->
            <div
                v-if="state === 'flipping'"
                class="flex flex-col items-center justify-center py-12"
            >
                <div class="coin-container">
                    <div class="coin flipping">
                        <!-- heads face -->
                        <div class="face heads">
                            <img src="/images/sinf-2026-sm.svg" alt="Heads" />
                        </div>
                        <!-- tails face -->
                        <div class="face tails">
                            <img
                                src="/images/sinf-2026-tails.svg"
                                alt="Tails"
                            />
                        </div>
                    </div>
                </div>
                <p
                    class="mt-8 animate-pulse font-mono text-sm font-semibold tracking-wide text-amber-400"
                >
                    A lançar a moeda...
                </p>
            </div>

            <!-- State: Waiting for opponent (polling GET /arcade/coin-flip/{id}/status every 2s) -->
            <div
                v-else-if="state === 'waiting'"
                class="flex flex-col items-center justify-center py-8 text-center"
            >
                <div class="coin-container mb-4">
                    <div class="coin coin-idle">
                        <div class="face heads">
                            <img src="/images/sinf-2026-sm.svg" alt="Heads" />
                        </div>
                        <div class="face tails">
                            <img
                                src="/images/sinf-2026-tails.svg"
                                alt="Tails"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-white">
                    <Loader2 class="h-4 w-4 animate-spin text-amber-400" />
                    <h4 class="text-base font-bold">
                        À espera de um adversário...
                    </h4>
                </div>

                <p class="mt-1 text-xs text-neutral-400">
                    A tua aposta:
                    <strong class="font-mono text-white">{{ wager }}</strong>
                    pts
                </p>

                <p
                    class="mt-3 flex items-center gap-1.5 font-mono text-xs text-neutral-400"
                >
                    <Clock :size="13" class="text-neutral-500" />
                    <span>À espera há {{ waitingSeconds }}s</span>
                </p>

                <!-- Cancel button: appears after 30s of waiting -->
                <div v-if="canCancel" class="mt-6 flex flex-col items-center">
                    <button
                        type="button"
                        :disabled="isCancelling"
                        class="inline-flex items-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-xs font-semibold text-red-400 transition hover:bg-red-500/20 hover:text-red-300 disabled:opacity-50"
                        @click="cancelGame"
                    >
                        <Loader2
                            v-if="isCancelling"
                            class="h-3.5 w-3.5 animate-spin"
                        />
                        <span>Cancelar e recuperar aposta</span>
                    </button>
                    <span class="mt-1.5 text-[11px] text-neutral-500">
                        Reembolso de acordo com as regras de cancelamento
                    </span>
                </div>
            </div>

            <!-- States: Idle, Starting, Joining -->
            <div v-else class="space-y-6 pt-5">
                <!-- Mode Switch Tabs -->
                <div class="flex rounded-xl bg-white/5 p-1">
                    <button
                        type="button"
                        :disabled="isWagerActionInProgress"
                        :class="[
                            'flex-1 rounded-lg py-1.5 text-xs font-semibold transition',
                            state === 'idle' || state === 'starting'
                                ? 'bg-amber-500/20 text-amber-400 shadow-sm'
                                : 'text-neutral-400 hover:text-white disabled:opacity-50',
                        ]"
                        @click="state = 'idle'"
                    >
                        Criar jogo
                    </button>
                    <button
                        type="button"
                        :disabled="isWagerActionInProgress"
                        :class="[
                            'flex flex-1 items-center justify-center gap-1.5 rounded-lg py-1.5 text-xs font-semibold transition',
                            state === 'joining'
                                ? 'bg-amber-500/20 text-amber-400 shadow-sm'
                                : 'text-neutral-400 hover:text-white disabled:opacity-50',
                        ]"
                        @click="state = 'joining'"
                    >
                        <span>Aderir a jogo</span>
                        <span
                            v-if="lobbySessions.length > 0"
                            class="py-0.2 rounded-full bg-amber-500/30 px-1.5 font-mono text-[10px] text-amber-300"
                        >
                            {{ lobbySessions.length }}
                        </span>
                    </button>
                </div>

                <!-- Idle / Starting: Criar jogo Form -->
                <div
                    v-if="state === 'idle' || state === 'starting'"
                    class="space-y-4"
                >
                    <WagerInput
                        v-model="wager"
                        :min="game.min_wager"
                        :max="game.max_wager"
                        :balance="currentBalance"
                        :disabled="isWagerActionInProgress"
                    />

                    <PrimaryButton
                        padding="py-2.5 px-4"
                        text-size="text-xs"
                        class="w-full justify-center gap-2"
                        :disabled="
                            isWagerActionInProgress ||
                            wager < game.min_wager ||
                            wager > game.max_wager ||
                            wager > currentBalance
                        "
                        @click="startGame"
                    >
                        <Loader2
                            v-if="state === 'starting'"
                            class="h-4 w-4 animate-spin"
                        />
                        <span>{{
                            state === "starting"
                                ? "A criar jogo..."
                                : "Criar jogo"
                        }}</span>
                    </PrimaryButton>
                </div>

                <!-- Open Sessions Section ("Aderir a jogo") -->
                <div class="space-y-3 pt-2">
                    <div
                        class="flex items-center justify-between border-t border-white/5 pt-4"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-bold tracking-wider text-neutral-400 uppercase"
                            >
                                Salas Abertas
                            </span>
                            <span
                                v-if="lobbySessions.length > 0"
                                class="rounded-full bg-white/10 px-1.5 py-0.5 font-mono text-[10px] text-neutral-300"
                            >
                                {{ lobbySessions.length }}
                            </span>
                        </div>
                        <button
                            type="button"
                            :disabled="
                                isLoadingLobby || isWagerActionInProgress
                            "
                            class="flex items-center gap-1 text-[11px] text-neutral-400 hover:text-white disabled:opacity-50"
                            @click="fetchLobby"
                        >
                            <RefreshCw
                                :size="12"
                                :class="{ 'animate-spin': isLoadingLobby }"
                            />
                            <span>Atualizar</span>
                        </button>
                    </div>

                    <!-- Lobby List -->
                    <div
                        v-if="lobbySessions.length > 0"
                        class="max-h-56 space-y-2 overflow-y-auto pr-1"
                    >
                        <div
                            v-for="sessionItem in lobbySessions"
                            :key="sessionItem.id"
                            class="flex items-center justify-between rounded-2xl border border-white/5 bg-white/5 p-3 transition hover:border-white/10"
                        >
                            <div class="flex items-center gap-2.5">
                                <img
                                    v-if="
                                        sessionItem.enrollment?.participant
                                            ?.user?.profile_photo_url
                                    "
                                    :src="
                                        sessionItem.enrollment.participant.user
                                            .profile_photo_url
                                    "
                                    :alt="
                                        sessionItem.enrollment.participant.user
                                            .name
                                    "
                                    class="h-8 w-8 rounded-full border border-white/10 object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-neutral-400"
                                >
                                    <UserIcon :size="14" />
                                </div>

                                <div>
                                    <div
                                        class="text-xs font-semibold text-white"
                                    >
                                        {{
                                            sessionItem.enrollment?.participant
                                                ?.user?.name || "Jogador"
                                        }}
                                    </div>
                                    <div
                                        class="flex items-center gap-1 font-mono text-[11px] text-neutral-400"
                                    >
                                        <span>Aposta:</span>
                                        <span class="font-bold text-amber-400">
                                            {{ sessionItem.wager }}
                                        </span>
                                        <span>pts</span>
                                    </div>
                                </div>
                            </div>

                            <PrimaryButton
                                padding="px-3.5 py-1.5"
                                text-size="text-xs"
                                :disabled="
                                    isWagerActionInProgress ||
                                    currentBalance < sessionItem.wager
                                "
                                @click="joinGame(sessionItem)"
                            >
                                <Loader2
                                    v-if="joiningSessionId === sessionItem.id"
                                    class="mr-1 h-3 w-3 animate-spin"
                                />
                                <span>{{
                                    currentBalance < sessionItem.wager
                                        ? "Sem saldo"
                                        : "Aderir"
                                }}</span>
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Empty Lobby State -->
                    <div
                        v-else-if="!isLoadingLobby"
                        class="rounded-2xl border border-dashed border-white/10 bg-white/2 p-6 text-center"
                    >
                        <p class="text-xs text-neutral-400">
                            Não há jogos abertos de momento.
                        </p>
                        <p class="mt-1 text-[11px] text-neutral-500">
                            Cria um jogo acima e aguarda por um adversário!
                        </p>
                    </div>

                    <!-- Loading Lobby State -->
                    <div
                        v-else
                        class="flex items-center justify-center py-6 text-xs text-neutral-400"
                    >
                        <Loader2
                            class="mr-2 h-4 w-4 animate-spin text-amber-400"
                        />
                        <span>A procurar salas...</span>
                    </div>
                </div>
            </div>

            <!-- Result Overlay -->
            <ResultOverlay
                :show="state === 'result'"
                :outcome="outcome"
                :wager="wager"
                :payout="payout"
                @close="handleClose"
                @play-again="handlePlayAgain"
            />
        </div>
    </Modal>
</template>

<style scoped>
.coin-container {
    perspective: 1000px;
}

.coin {
    width: 120px;
    height: 120px;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 1.5s ease-in-out;
}

@keyframes coin-flip {
    from {
        transform: rotateY(0deg);
    }
    to {
        transform: rotateY(1800deg);
    }
}

.coin.flipping {
    animation: coin-flip 1.5s cubic-bezier(0.4, 0.2, 0.2, 1) forwards;
}

.coin.coin-idle {
    animation: gentle-spin 4s ease-in-out infinite alternate;
}

@keyframes gentle-spin {
    from {
        transform: rotateY(-20deg) scale(0.98);
    }
    to {
        transform: rotateY(20deg) scale(1.02);
    }
}

.face {
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: radial-gradient(circle at 35% 35%, #2a2a2a, #121212);
    border: 4px solid rgba(245, 158, 11, 0.4);
    box-shadow:
        0 10px 25px -5px rgba(0, 0, 0, 0.6),
        inset 0 2px 4px rgba(255, 255, 255, 0.15),
        inset 0 -2px 4px rgba(0, 0, 0, 0.4);
}

.face img {
    width: 58px;
    height: 58px;
    object-fit: contain;
}

.heads {
    transform: rotateY(0deg);
}

.tails {
    transform: rotateY(180deg);
}
</style>
