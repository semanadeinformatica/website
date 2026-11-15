<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { route } from "ziggy-js";
import type ArcadeGame from "@/Types/Arcade";
import WagerInput from "@/Components/Arcade/WagerInput.vue";
import GameHeader from "@/Components/Arcade/GameHeader.vue";
import GameResult from "@/Components/Arcade/GameResult.vue";
import InfoBox from "@/Components/UI/InfoBox.vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { Bug, ShieldCheck } from "@lucide/vue";

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

const maxSafeClicks = computed(() => {
    // The backend also ends the round once the multiplier ladder runs out.
    return Math.min(9 - bombCount.value, clickMultipliers.value.length);
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

        localBalance.value =
            typeof response.data.balance === "number"
                ? response.data.balance
                : Math.max(0, localBalance.value - wager.value);
        emit("update:balance", localBalance.value);
        router.reload({ only: ["points", "enrollment_points"] });
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
            router.reload({ only: ["points", "enrollment_points"] });
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
                router.reload({ only: ["points", "enrollment_points"] });
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
        router.reload({ only: ["points", "enrollment_points"] });
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
            router.reload({ only: ["points", "enrollment_points"] });
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
    <Modal
        :show="show"
        max-width="lg"
        :close-disabled="isActionLoading"
        :click-to-close="!isActionLoading"
        :esc-to-close="!isActionLoading"
        @close="handleCloseRequest"
    >
        <template #header>
            <GameHeader
                :game="game"
                :show-limits="stage === 'idle'"
                description="Revela nós seguros e recolhe os ganhos antes de encontrares um bug."
            />
        </template>

        <div class="space-y-4">
            <InfoBox v-if="errorMessage" variant="danger">
                {{ errorMessage }}
            </InfoBox>

            <!-- Idle: wager selection -->
            <div v-if="stage === 'idle'" class="space-y-5">
                <InfoBox title="Como jogar">
                    Existem
                    <strong class="text-white">
                        {{ bombCount }} bugs críticos
                    </strong>
                    à solta no sistema. Revela nós seguros para aumentar o
                    multiplicador e recolhe os ganhos antes de encontrares um
                    bug. Se saíres a meio, os ganhos são recolhidos
                    automaticamente.
                </InfoBox>

                <WagerInput
                    v-model="wager"
                    :min="game.min_wager"
                    :max="game.max_wager"
                    :balance="localBalance"
                    :disabled="isActionLoading"
                />

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

            <!-- Playing / game over -->
            <div v-else class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div
                        class="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3"
                    >
                        <span
                            class="text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
                        >
                            Multiplicador
                        </span>
                        <p class="mt-0.5 text-lg font-semibold text-white">
                            ×{{ currentMultiplier.toFixed(2) }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3"
                    >
                        <span
                            class="text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
                        >
                            Ganho potencial
                        </span>
                        <p
                            class="mt-0.5 flex items-center gap-1.5 text-lg font-semibold text-white"
                        >
                            {{ potentialPayout }}
                            <img
                                class="h-3.5 w-3.5 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </p>
                    </div>
                </div>

                <!-- 3x3 board -->
                <div
                    class="mx-auto grid max-w-[300px] grid-cols-3 gap-3 sm:max-w-[320px]"
                >
                    <button
                        v-for="cell in grid"
                        :key="cell.index"
                        type="button"
                        :aria-label="`Revelar célula ${cell.index + 1}`"
                        :disabled="
                            cell.state !== 'unrevealed' ||
                            isActionLoading ||
                            stage !== 'playing'
                        "
                        :class="[
                            'group relative flex aspect-square items-center justify-center rounded-2xl border transition-colors duration-200',
                            cell.state === 'unrevealed'
                                ? 'border-white/10 bg-white/[0.03] hover:border-white/20 hover:bg-white/[0.06] active:scale-[0.98] disabled:pointer-events-none'
                                : '',
                            cell.state === 'scanning'
                                ? 'animate-pulse border-white/20 bg-white/[0.06]'
                                : '',
                            cell.state === 'safe'
                                ? 'border-white/15 bg-white/[0.05] text-neutral-200'
                                : '',
                            cell.state === 'bomb_detonated'
                                ? 'border-red-500/40 bg-red-500/10 text-red-300'
                                : '',
                            cell.state === 'bomb_revealed'
                                ? 'border-white/10 bg-white/[0.02] text-neutral-600'
                                : '',
                        ]"
                        @click="clickCell(cell)"
                    >
                        <div
                            v-if="cell.state === 'unrevealed'"
                            class="h-1.5 w-1.5 rounded-full bg-white/20 transition-colors group-hover:bg-white/40"
                        />

                        <div
                            v-else-if="cell.state === 'scanning'"
                            class="h-5 w-5 animate-spin rounded-full border-2 border-white/50 border-t-transparent"
                        />

                        <ShieldCheck
                            v-else-if="cell.state === 'safe'"
                            :size="38"
                            class="animate-shield-pop stroke-[1.8]"
                        />

                        <Bug
                            v-else-if="cell.state === 'bomb_detonated'"
                            :size="26"
                            class="animate-pulse stroke-[2]"
                        />

                        <Bug
                            v-else-if="cell.state === 'bomb_revealed'"
                            :size="22"
                            class="stroke-[2]"
                        />
                    </button>
                </div>

                <!-- Playing controls -->
                <div v-if="stage === 'playing'" class="space-y-3">
                    <div
                        class="flex items-center justify-between text-xs text-neutral-400"
                    >
                        <span>
                            Nós seguros
                            <strong class="text-white">{{ clicks }}</strong>
                            / {{ maxSafeClicks }}
                        </span>
                    </div>

                    <PrimaryButton
                        :disabled="!canCashout || isActionLoading"
                        class="w-full justify-center"
                        @click="handleCashout"
                    >
                        <span
                            v-if="canCashout"
                            class="inline-flex items-center gap-1.5"
                        >
                            Recolher
                            <strong>{{ potentialPayout }}</strong>
                            <img
                                class="h-3.5 w-3.5 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </span>
                        <span v-else> Revela um nó seguro para recolher </span>
                    </PrimaryButton>
                </div>

                <!-- Game over -->
                <GameResult
                    v-else-if="stage === 'game_over'"
                    :outcome="resultOutcome"
                    :wager="wager"
                    :payout="resultPayout"
                    @play-again="handlePlayAgain"
                />
            </div>
        </div>
    </Modal>
</template>

<style scoped>
@keyframes shieldPop {
    from {
        transform: scale(0.7);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-shield-pop {
    animation: shieldPop 0.22s ease-out;
}
</style>
