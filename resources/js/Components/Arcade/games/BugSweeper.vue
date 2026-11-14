<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { Bug, AlertCircle, ShieldCheck, X } from "@lucide/vue";

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
    (e: "update:balance", balance: number): void;
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

interface Cell {
    index: number;
    state:
        "unrevealed" | "scanning" | "safe" | "bomb_detonated" | "bomb_revealed";
    multiplier?: number;
}

type GameStage = "idle" | "playing" | "game_over";

const stage = ref<GameStage>("idle");
const wager = ref<number>(props.game.min_wager);
const sessionId = ref<number | null>(null);
const bombCount = ref<number>(
    (props.game.payout_config?.bomb_count as number) ?? 3,
);
const clickMultipliers = ref<number[]>(
    (props.game.payout_config?.click_multipliers as number[]) ?? [
        1.2, 1.5, 2.0, 3.0, 5.0,
    ],
);

const grid = ref<Cell[]>([]);
const currentMultiplier = ref<number>(1.0);
const clicks = ref<number>(0);
const canCashout = ref<boolean>(false);
const isActionLoading = ref<boolean>(false);
const errorMessage = ref<string | null>(null);

// End-game result state
const resultOutcome = ref<"win" | "loss" | "cashout" | null>(null);
const resultPayout = ref<number>(0);

const potentialPayout = computed(() => {
    if (clicks.value === 0) return wager.value;
    return Math.floor(wager.value * currentMultiplier.value);
});

const potentialProfit = computed(() => {
    return potentialPayout.value - wager.value;
});

const maxSafeClicks = computed(() => {
    return 9 - bombCount.value;
});

function initGrid() {
    grid.value = Array.from({ length: 9 }, (_, index) => ({
        index,
        state: "unrevealed",
    }));
}

async function startGame() {
    if (isActionLoading.value) return;
    errorMessage.value = null;
    isActionLoading.value = true;

    try {
        const response = await axios.post(route("arcade.bug-sweeper.start"), {
            wager: wager.value,
        });

        sessionId.value = response.data.session_id;
        bombCount.value = response.data.bomb_count ?? bombCount.value;
        clickMultipliers.value =
            response.data.click_multipliers ?? clickMultipliers.value;

        initGrid();
        currentMultiplier.value = 1.0;
        clicks.value = 0;
        canCashout.value = false;
        resultOutcome.value = null;
        resultPayout.value = 0;
        stage.value = "playing";

        localBalance.value = Math.max(0, localBalance.value - wager.value);
        emit("update:balance", localBalance.value);
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Ocorreu um erro ao iniciar o jogo.";
        }
    } finally {
        isActionLoading.value = false;
    }
}

async function clickCell(cell: Cell) {
    if (
        isActionLoading.value ||
        cell.state !== "unrevealed" ||
        stage.value !== "playing" ||
        !sessionId.value
    ) {
        return;
    }

    errorMessage.value = null;
    cell.state = "scanning";
    isActionLoading.value = true;

    try {
        const response = await axios.post(
            route("arcade.bug-sweeper.click", { session: sessionId.value }),
            {
                cell_index: cell.index,
            },
        );

        const data = response.data;

        if (data.is_bomb) {
            cell.state = "bomb_detonated";
            stage.value = "game_over";

            // Reveal all other bombs passively
            const bombPositions: number[] = data.bomb_positions ?? [];
            grid.value.forEach((c) => {
                if (bombPositions.includes(c.index) && c.index !== cell.index) {
                    c.state = "bomb_revealed";
                }
            });

            resultOutcome.value = "loss";
            resultPayout.value = 0;
            router.reload({ only: ["points"] });
        } else {
            cell.state = "safe";
            cell.multiplier = data.current_multiplier;
            currentMultiplier.value = data.current_multiplier;
            clicks.value = data.clicks;
            canCashout.value = data.can_cashout ?? false;

            if (data.outcome === "cashout") {
                // Auto-cashout upon board clearance
                stage.value = "game_over";
                canCashout.value = false;

                const bombPositions: number[] = data.bomb_positions ?? [];
                grid.value.forEach((c) => {
                    if (bombPositions.includes(c.index)) {
                        c.state = "bomb_revealed";
                    }
                });

                resultOutcome.value = "win";
                resultPayout.value = data.payout;
                localBalance.value += data.payout;
                emit("update:balance", localBalance.value);
                router.reload({ only: ["points"] });
            }
        }
    } catch (err: unknown) {
        cell.state = "unrevealed";
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Erro ao revelar a célula.";
        }
    } finally {
        isActionLoading.value = false;
    }
}

async function handleCashout() {
    if (
        isActionLoading.value ||
        !canCashout.value ||
        stage.value !== "playing" ||
        !sessionId.value
    ) {
        return;
    }

    errorMessage.value = null;
    isActionLoading.value = true;

    try {
        const response = await axios.post(
            route("arcade.bug-sweeper.cashout", { session: sessionId.value }),
        );

        const data = response.data;
        stage.value = "game_over";
        canCashout.value = false;

        const bombPositions: number[] = data.bomb_positions ?? [];
        grid.value.forEach((c) => {
            if (bombPositions.includes(c.index)) {
                c.state = "bomb_revealed";
            }
        });

        resultOutcome.value = "cashout";
        resultPayout.value = data.payout;
        localBalance.value += data.payout;
        emit("update:balance", localBalance.value);
        router.reload({ only: ["points"] });
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = "Erro ao recolher os pontos.";
        }
    } finally {
        isActionLoading.value = false;
    }
}

async function handleRebet() {
    if (localBalance.value < wager.value || isActionLoading.value) {
        return;
    }
    resultOutcome.value = null;
    resultPayout.value = 0;
    errorMessage.value = null;
    initGrid();
    await startGame();
}

function handlePlayAgain() {
    resultOutcome.value = null;
    resultPayout.value = 0;
    stage.value = "idle";
    errorMessage.value = null;
    initGrid();
}

async function handleCloseRequest() {
    // Never close while a request is in flight: for startGame this would
    // charge the wager without showing the board, and for cell clicks it
    // would hide the outcome.
    if (isActionLoading.value) {
        return;
    }

    if (
        stage.value === "playing" &&
        sessionId.value &&
        clicks.value > 0 &&
        canCashout.value &&
        !isActionLoading.value
    ) {
        try {
            isActionLoading.value = true;
            const response = await axios.post(
                route("arcade.bug-sweeper.cashout", {
                    session: sessionId.value,
                }),
            );
            const data = response.data;
            localBalance.value += data.payout;
            emit("update:balance", localBalance.value);
            router.reload({ only: ["points"] });
        } catch {
            // Se falhar o pedido, o backend auto-recolhe no próximo arranque
        } finally {
            isActionLoading.value = false;
        }
    }
    emit("close");
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
            <div class="flex items-center gap-3">
                <div
                    class="relative flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-white/15 bg-white/[0.08] p-1.5 shadow-md"
                >
                    <img
                        src="/images/arcade/BugSweeper-Icon.png"
                        :alt="game.name"
                        class="h-full w-full object-contain drop-shadow-[0_0_4px_rgba(147,70,255,0.35)]"
                    />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">
                        {{ game.name }}
                    </h3>
                    <div
                        class="flex items-center gap-1 font-mono text-xs text-neutral-400"
                    >
                        <span>{{ game.min_wager }}–{{ game.max_wager }}</span>
                        <img
                            class="h-3 w-3 object-contain"
                            alt="SINFrão"
                            title="SINFrão"
                            src="/images/sinf-2026-sm.svg"
                        />
                    </div>
                </div>
            </div>
            <button
                type="button"
                aria-label="Fechar"
                :disabled="isActionLoading"
                class="rounded-lg p-1.5 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white disabled:pointer-events-none disabled:opacity-40"
                @click="handleCloseRequest"
            >
                <X :size="18" />
            </button>
        </div>

        <!-- Global Error Alert -->
        <div
            v-if="errorMessage"
            class="mt-4 flex items-center gap-2 rounded-xl border border-red-500/30 bg-red-950/40 p-3 text-xs font-medium text-red-300"
        >
            <AlertCircle :size="14" class="shrink-0 text-red-400" />
            <span>{{ errorMessage }}</span>
        </div>

        <!-- Stage: Idle (Wager Selection) -->
        <div v-if="stage === 'idle'" class="space-y-6 py-6">
            <div class="space-y-2 text-center">
                <p class="text-sm font-medium text-neutral-200">
                    Depura o sistema sem rebentar com o código!
                </p>
                <p class="text-xs text-neutral-400">
                    Existem
                    <strong class="text-amber-400"
                        >{{ bombCount }} bugs críticos</strong
                    >
                    à solta no sistema! Descobre os nós seguros para multiplicar
                    os teus pontos e não deixes que os bugs te roubem os
                    SINFrões.
                </p>
                <p class="pt-1 text-[11px] text-neutral-400">
                    Se saíres a meio do jogo, os teus ganhos são recolhidos
                    automaticamente.
                </p>
            </div>

            <WagerInput
                v-model="wager"
                :min="game.min_wager"
                :max="game.max_wager"
                :balance="localBalance"
                :disabled="isActionLoading"
            />

            <div class="pt-2">
                <PrimaryButton
                    :disabled="
                        isActionLoading ||
                        wager > localBalance ||
                        wager < game.min_wager
                    "
                    class="w-full justify-center"
                    @click="startGame"
                >
                    Jogar
                </PrimaryButton>
            </div>
        </div>

        <!-- Stage: Playing or Game Over Board -->
        <div v-else class="space-y-5 py-4">
            <!-- Minimalist HUD -->
            <div
                class="flex items-center justify-between rounded-2xl border border-white/5 bg-white/[0.03] px-4 py-3"
            >
                <!-- Multiplier Counter -->
                <div class="flex flex-col">
                    <span
                        class="text-[11px] font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        Multiplicador
                    </span>
                    <span class="font-mono text-2xl font-black text-[#9346ff]">
                        ×{{ currentMultiplier.toFixed(2) }}
                    </span>
                </div>

                <!-- Live Profit / Payout Preview -->
                <div class="flex flex-col items-end">
                    <span
                        class="text-[11px] font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        Ganhos Potenciais
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="font-mono text-xl font-bold text-white">
                            {{ potentialPayout }}
                        </span>
                        <img
                            class="h-4 w-4 object-contain"
                            alt="SINFrão"
                            title="SINFrão"
                            src="/images/sinf-2026-sm.svg"
                        />
                        <span
                            v-if="clicks > 0"
                            class="font-mono text-xs font-semibold text-[#9346ff]"
                        >
                            (+{{ potentialProfit }})
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3x3 Grid (Always 100% visible) -->
            <div class="relative py-2">
                <div
                    class="mx-auto grid max-w-[300px] grid-cols-3 gap-3 sm:max-w-[320px]"
                >
                    <button
                        v-for="cell in grid"
                        :key="cell.index"
                        type="button"
                        :disabled="
                            cell.state !== 'unrevealed' ||
                            isActionLoading ||
                            stage !== 'playing'
                        "
                        :class="[
                            'group relative flex aspect-square items-center justify-center rounded-2xl border transition-all duration-300',
                            // Unrevealed state
                            cell.state === 'unrevealed'
                                ? 'border-white/10 bg-white/5 shadow-inner hover:scale-105 hover:border-amber-400/50 hover:bg-white/10 active:scale-95 disabled:pointer-events-none'
                                : '',
                            // Scanning / Decompile in-flight state
                            cell.state === 'scanning'
                                ? 'scale-95 animate-pulse border-amber-400/70 bg-amber-400/10'
                                : '',
                            // Safe cell state: Antivirus Shield
                            cell.state === 'safe'
                                ? 'border-[#6a00ff]/60 bg-[#4300a3]/40 text-[#9346ff] shadow-[0_0_20px_rgba(106,0,255,0.4)]'
                                : '',
                            // Bomb Detonated by user: Fixed dark red background, lines & bug pulse
                            cell.state === 'bomb_detonated'
                                ? 'border-[#9a0036] bg-[#630022]/40 text-[#d6336c]'
                                : '',
                            // Bomb Revealed passively at game over
                            cell.state === 'bomb_revealed'
                                ? 'border-[#9a0036]/40 bg-[#630022]/30 text-[#d6336c]/80'
                                : '',
                        ]"
                        @click="clickCell(cell)"
                    >
                        <!-- Unrevealed Icon hint on hover -->
                        <div
                            v-if="cell.state === 'unrevealed'"
                            class="h-2 w-2 rounded-full bg-white/20 transition-all duration-200 group-hover:scale-150 group-hover:bg-amber-400"
                        />

                        <!-- Scanning State Spinner -->
                        <div
                            v-else-if="cell.state === 'scanning'"
                            class="h-5 w-5 animate-spin rounded-full border-2 border-amber-400 border-t-transparent"
                        />

                        <!-- Safe Clean Node: Antivirus Shield (SINF Secondary Roxo) -->
                        <div
                            v-else-if="cell.state === 'safe'"
                            class="flex items-center justify-center"
                        >
                            <ShieldCheck
                                :size="42"
                                class="animate-shield-pop stroke-[2.2] text-[#9346ff]"
                            />
                        </div>

                        <!-- Bomb Detonated: Microchip Package with Bug in Center -->
                        <div
                            v-else-if="cell.state === 'bomb_detonated'"
                            class="relative flex h-full w-full items-center justify-center p-1.5"
                        >
                            <img
                                src="/images/arcade/microchip-detonated.svg"
                                alt="Microchip Detonado"
                                class="pointer-events-none absolute inset-0 h-full w-full select-none"
                            />

                            <!-- Bug Icon Centered in Microchip Core -->
                            <Bug
                                :size="24"
                                class="animate-circuit-pulse relative z-10 stroke-[2.5]"
                            />
                        </div>

                        <!-- Bomb Revealed: Microchip Package with Bug in Center (Passive) -->
                        <div
                            v-else-if="cell.state === 'bomb_revealed'"
                            class="relative flex h-full w-full items-center justify-center p-1.5 text-[#d6336c] opacity-85"
                        >
                            <img
                                src="/images/arcade/microchip-revealed.svg"
                                alt="Microchip Revelado"
                                class="pointer-events-none absolute inset-0 h-full w-full select-none"
                            />

                            <Bug
                                :size="24"
                                class="relative z-10 stroke-[2.5]"
                            />
                        </div>
                    </button>
                </div>
            </div>

            <!-- Action Controls: Cashout Button (Playing) or Inline Cyber Result Panel (Game Over) -->
            <div class="space-y-3 pt-2">
                <!-- Playing State Controls -->
                <template v-if="stage === 'playing'">
                    <div
                        class="flex items-center justify-between text-xs text-neutral-400"
                    >
                        <span>
                            Progresso:
                            <strong class="text-white">{{ clicks }}</strong> /
                            {{ maxSafeClicks }} nós seguros
                        </span>
                        <span
                            v-if="canCashout"
                            class="font-medium text-amber-400"
                        >
                            Pronto para recolher
                        </span>
                    </div>

                    <PrimaryButton
                        :disabled="!canCashout || isActionLoading"
                        class="w-full justify-center"
                        @click="handleCashout"
                    >
                        <span v-if="canCashout"> Recolher </span>
                        <span v-else>
                            Revela pelo menos 1 nó seguro para recolher
                        </span>
                    </PrimaryButton>
                </template>

                <!-- Game Over: Integrated Cyber Result Panel (Grid remains 100% visible) -->
                <div v-else-if="stage === 'game_over'" class="space-y-3">
                    <!-- Loss Banner (SINF Primary Rubi) -->
                    <div
                        v-if="resultOutcome === 'loss'"
                        class="relative overflow-hidden rounded-2xl border border-[#9a0036]/50 bg-[#630022]/50 p-4 text-center shadow-[0_0_25px_rgba(154,0,54,0.25)] backdrop-blur-md"
                    >
                        <h4
                            class="text-base font-black tracking-tight text-[#d6336c] sm:text-lg"
                        >
                            Foste hackeado por causa de um bug!
                        </h4>
                        <p
                            class="mt-1 flex items-center justify-center gap-1.5 text-xs text-neutral-300 sm:text-sm"
                        >
                            <span>O sistema colapsou e perdeste</span>
                            <strong class="font-mono font-bold text-white">{{
                                wager
                            }}</strong>
                            <img
                                class="inline h-4 w-4 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </p>
                    </div>

                    <!-- Win / Cashout Banner (SINF Secondary Roxo) -->
                    <div
                        v-else-if="
                            resultOutcome === 'win' ||
                            resultOutcome === 'cashout'
                        "
                        class="relative overflow-hidden rounded-2xl border border-[#6a00ff]/50 bg-[#4300a3]/40 p-4 text-center shadow-[0_0_25px_rgba(106,0,255,0.25)] backdrop-blur-md"
                    >
                        <h4
                            class="text-base font-black tracking-tight text-[#9346ff] sm:text-lg"
                        >
                            Sistema protegido!
                        </h4>
                        <p
                            class="mt-1 flex items-center justify-center gap-1.5 text-xs text-neutral-300 sm:text-sm"
                        >
                            <span
                                >Depuração concluída com sucesso.
                                Recolheste</span
                            >
                            <strong class="font-mono font-bold text-[#9346ff]"
                                >+{{ resultPayout }}</strong
                            >
                            <img
                                class="inline h-4 w-4 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </p>
                    </div>

                    <!-- End-Game Action Buttons: Change Wager & Rebet -->
                    <div class="pt-1">
                        <div
                            v-if="localBalance >= wager"
                            class="grid grid-cols-2 gap-2.5"
                        >
                            <!-- Change Wager Button (Left) -->
                            <button
                                type="button"
                                class="flex items-center justify-center rounded-xl border border-white/10 bg-white/5 py-2.5 text-xs font-semibold text-neutral-300 transition-colors hover:bg-white/10 hover:text-white"
                                @click="handlePlayAgain"
                            >
                                Mudar Aposta
                            </button>

                            <!-- Rebet Button (Right) -->
                            <button
                                type="button"
                                :disabled="isActionLoading"
                                class="flex items-center justify-center gap-1.5 rounded-xl border border-white/10 bg-white/5 py-2.5 text-xs font-semibold text-neutral-200 transition-colors hover:border-white/20 hover:bg-white/10 hover:text-white disabled:pointer-events-none disabled:opacity-50"
                                @click="handleRebet"
                            >
                                <span>Reapostar</span>
                                <span class="font-mono font-bold">{{
                                    wager
                                }}</span>
                                <img
                                    class="h-3.5 w-3.5 object-contain"
                                    alt="SINFrão"
                                    src="/images/sinf-2026-sm.svg"
                                />
                            </button>
                        </div>

                        <!-- Fallback when balance is insufficient: Change Wager takes full width -->
                        <button
                            v-else
                            type="button"
                            class="flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/5 py-2.5 text-xs font-semibold text-neutral-200 transition-colors hover:bg-white/10 hover:text-white"
                            @click="handlePlayAgain"
                        >
                            Mudar Aposta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes shieldPop {
    0% {
        transform: scale(0.2);
        opacity: 0;
        filter: drop-shadow(0 0 0 rgba(106, 0, 255, 0));
    }
    70% {
        transform: scale(1.18);
        opacity: 1;
        filter: drop-shadow(0 0 16px rgba(147, 70, 255, 0.85));
    }
    100% {
        transform: scale(1);
        filter: drop-shadow(0 0 8px rgba(106, 0, 255, 0.45));
    }
}

.animate-shield-pop {
    animation: shieldPop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes bugReveal {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 0.85;
    }
}

.animate-bug-reveal {
    animation: bugReveal 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    20%,
    60% {
        transform: translateX(-4px);
    }
    40%,
    80% {
        transform: translateX(4px);
    }
}

@keyframes circuitColorPulse {
    0%,
    100% {
        color: #d6336c;
        filter: drop-shadow(0 0 4px rgba(214, 51, 108, 0.6));
    }
    50% {
        color: #ffffff;
        filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.8));
    }
}

.animate-circuit-pulse {
    animation: circuitColorPulse 1.5s ease-in-out infinite;
}

.animate-electric-cell {
    border-color: #9a0036;
    animation: shake 0.4s ease-in-out;
}
</style>
