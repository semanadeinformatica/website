<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import Modal from "@/Components/UI/Modal.vue";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import GameHeader from "@/Components/Arcade/GameHeader.vue";
import GameResult from "@/Components/Arcade/GameResult.vue";
import InfoBox from "@/Components/UI/InfoBox.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { Loader2, RefreshCw, User as UserIcon } from "@lucide/vue";

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
// `points` is refreshed after every wager/result and already includes
// payouts, so it is the single source of truth for the balance.
watch(
    () => props.points,
    (val) => {
        currentBalance.value = val;
    },
);

let startRequestId = 0;
let pollInterval: ReturnType<typeof setInterval> | null = null;
const isCancelling = ref(false);

const PENDING_SESSION_KEY = "sinf_arcade_coin_flip_session";

interface StoredSession {
    id: number;
    wager: number;
}

function savePendingSession(id: number, bet: number) {
    try {
        const item: StoredSession = {
            id,
            wager: bet,
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

const activeTab = computed({
    get: () => (state.value === "joining" ? "join" : "create"),
    set: (value: string | number | null) => {
        if (isWagerActionInProgress.value) return;
        state.value = value === "join" ? "joining" : "idle";
    },
});

const modeTabs = computed<PillOption[]>(() => [
    { id: "create", label: "Criar jogo" },
    {
        id: "join",
        label: "Aderir a jogo",
        count:
            lobbySessions.value.length > 0
                ? lobbySessions.value.length
                : undefined,
    },
]);

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

let resultTimeout: ReturnType<typeof setTimeout> | null = null;

function stopResultTimer() {
    if (resultTimeout) {
        clearTimeout(resultTimeout);
        resultTimeout = null;
    }
}

const canDismiss = computed(
    () =>
        state.value !== "starting" &&
        state.value !== "flipping" &&
        joiningSessionId.value === null,
);

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
        router.reload({ only: ["points", "enrollment_points"] });
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
                clearPendingSession();
                state.value = "flipping";

                stopResultTimer();
                resultTimeout = setTimeout(() => {
                    if (polledSessionId !== sessionId.value) return;
                    outcome.value = data.outcome;
                    payout.value = data.payout;
                    state.value = "result";
                    router.reload({ only: ["points", "enrollment_points"] });
                }, 1500); // wait for flip animation
            }
        } catch (err: unknown) {
            if (axios.isAxiosError(err) && err.response?.status === 404) {
                if (polledSessionId !== sessionId.value) return;
                stopPolling();
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
        const joinedSessionId = session.id;
        stopResultTimer();
        resultTimeout = setTimeout(() => {
            if (joinedSessionId !== sessionId.value) return;
            outcome.value = data.outcome;
            payout.value =
                data.player_payout ??
                (data.outcome === "win" ? data.payout : 0);
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
        stopResultTimer();
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
    // Never dismiss a request that may already have charged the player:
    // closing mid-start/join would orphan the paid session.
    if (!canDismiss.value) return;

    startRequestId++;
    stopPolling();
    stopResultTimer();

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
    stopResultTimer();
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
        state.value = "waiting";
        startPolling();

        try {
            const res = await axios.get(
                route("arcade.coin-flip.status", { session: stored.id }),
            );
            const data = res.data;
            if (data.outcome && data.outcome !== "pending") {
                stopPolling();
                clearPendingSession();
                state.value = "flipping";

                const restoredSessionId = stored.id;
                stopResultTimer();
                resultTimeout = setTimeout(() => {
                    if (restoredSessionId !== sessionId.value) return;
                    outcome.value = data.outcome;
                    payout.value = data.payout;
                    state.value = "result";
                    router.reload({ only: ["points", "enrollment_points"] });
                }, 1500);
            }
        } catch (err: unknown) {
            if (axios.isAxiosError(err) && err.response?.status === 404) {
                stopPolling();
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
            stopResultTimer();
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
    stopResultTimer();
});
</script>

<template>
    <Modal
        :show="show"
        max-width="lg"
        :close-disabled="!canDismiss"
        :click-to-close="canDismiss"
        :esc-to-close="canDismiss"
        @close="handleClose"
    >
        <template #header>
            <GameHeader
                :game="game"
                :show-limits="
                    state === 'idle' ||
                    state === 'starting' ||
                    state === 'joining'
                "
                description="Desafia outro participante num duelo de moedas em tempo real."
            />
        </template>

        <div class="space-y-5">
            <InfoBox v-if="errorMessage" variant="danger">
                {{ errorMessage }}
            </InfoBox>

            <!-- Flipping -->
            <div
                v-if="state === 'flipping'"
                class="flex flex-col items-center justify-center py-12"
            >
                <div class="coin-container">
                    <div class="coin flipping">
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
                <p
                    class="mt-8 animate-pulse text-sm font-medium tracking-wide text-neutral-400"
                >
                    A lançar a moeda...
                </p>
            </div>

            <!-- Waiting for opponent -->
            <div
                v-else-if="state === 'waiting'"
                class="flex flex-col items-center justify-center py-4 text-center"
            >
                <div class="coin-container">
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

                <div class="mt-5 flex items-center gap-2 text-white">
                    <Loader2 class="h-4 w-4 animate-spin text-neutral-400" />
                    <h3 class="text-base font-semibold">
                        À espera de um adversário...
                    </h3>
                </div>

                <p class="mt-1 text-xs text-neutral-400">
                    A tua aposta:
                    <strong class="text-white">{{ wager }}</strong>
                    pts
                </p>

                <div
                    class="mt-4 flex w-full flex-col gap-3 rounded-2xl border border-white/8 bg-white/[0.03] p-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="space-y-0.5 text-left">
                        <p class="text-xs text-neutral-400">
                            Podes cancelar enquanto ninguém entrar na sala.
                        </p>
                        <p class="text-[11px] text-neutral-500">
                            A aposta é devolvida ao cancelar.
                        </p>
                    </div>

                    <PrimaryButton
                        padding="px-4 py-2"
                        text-size="text-xs"
                        :disabled="isCancelling"
                        @click="cancelGame"
                    >
                        <Loader2
                            v-if="isCancelling"
                            class="h-3.5 w-3.5 animate-spin"
                        />
                        {{ isCancelling ? "A cancelar..." : "Cancelar jogo" }}
                    </PrimaryButton>
                </div>
            </div>

            <!-- Result -->
            <GameResult
                v-else-if="state === 'result'"
                :outcome="outcome"
                :wager="wager"
                :payout="payout"
                @play-again="handlePlayAgain"
            />

            <!-- Idle / starting / joining -->
            <template v-else>
                <div class="flex justify-center">
                    <PillSelector
                        v-model="activeTab"
                        :items="modeTabs"
                        size="sm"
                        :wrap="false"
                    />
                </div>

                <!-- Create game -->
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
                        class="w-full justify-center"
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
                        {{
                            state === "starting"
                                ? "A criar jogo..."
                                : "Criar jogo"
                        }}
                    </PrimaryButton>
                </div>

                <!-- Join game -->
                <div v-else class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                        >
                            Salas abertas
                        </span>
                        <PrimaryButton
                            padding="px-3 py-1.5"
                            text-size="text-[11px]"
                            :disabled="
                                isLoadingLobby || isWagerActionInProgress
                            "
                            @click="fetchLobby"
                        >
                            <RefreshCw
                                :size="12"
                                :class="{ 'animate-spin': isLoadingLobby }"
                            />
                            Atualizar
                        </PrimaryButton>
                    </div>

                    <div
                        v-if="lobbySessions.length > 0"
                        class="max-h-56 space-y-2 overflow-y-auto pr-1"
                    >
                        <div
                            v-for="sessionItem in lobbySessions"
                            :key="sessionItem.id"
                            class="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/[0.03] p-3"
                        >
                            <div class="flex min-w-0 items-center gap-2.5">
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

                                <div class="min-w-0">
                                    <div
                                        class="truncate text-xs font-semibold text-white"
                                    >
                                        {{
                                            sessionItem.enrollment?.participant
                                                ?.user?.name || "Jogador"
                                        }}
                                    </div>
                                    <div
                                        class="flex items-center gap-1 text-[11px] text-neutral-500"
                                    >
                                        Aposta
                                        <span class="font-semibold text-white">
                                            {{ sessionItem.wager }}
                                        </span>
                                        pts
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
                                    class="h-3 w-3 animate-spin"
                                />
                                {{
                                    currentBalance < sessionItem.wager
                                        ? "Sem saldo"
                                        : "Aderir"
                                }}
                            </PrimaryButton>
                        </div>
                    </div>

                    <InfoBox v-else-if="!isLoadingLobby">
                        Não há jogos abertos de momento. Cria um jogo acima e
                        aguarda por um adversário.
                    </InfoBox>

                    <div
                        v-else
                        class="flex items-center justify-center py-6 text-xs text-neutral-400"
                    >
                        <Loader2
                            class="mr-2 h-4 w-4 animate-spin text-neutral-400"
                        />
                        A procurar salas...
                    </div>
                </div>
            </template>
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
    background: radial-gradient(circle at 35% 35%, #232428, #121316);
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow:
        0 8px 20px -8px rgba(0, 0, 0, 0.6),
        inset 0 1px 1px rgba(255, 255, 255, 0.08);
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
