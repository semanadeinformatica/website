<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { ArrowRight, Play, RotateCcw } from "@lucide/vue";
import type { SimulationPrize } from "@/Types/Raffle";

interface Props {
    prizes: SimulationPrize[];
    isSimulation: boolean;
}

const props = defineProps<Props>();

type Phase = "idle" | "rolling" | "revealed" | "finished";

const rowHeight = 96;
const reelDuration = 7400;

const phase = ref<Phase>("idle");
const currentIndex = ref(-1);
const revealedIds = ref<number[]>([]);
const confettiKey = ref(0);
const confettiVisible = ref(false);

const reelItems = ref<string[]>([]);
const reelWinnerIndex = ref(0);
const reelOffset = ref(0);
const reelTransition = ref("none");

let activeRun = 0;
let confettiToken = 0;
const timers: number[] = [];

// The presentation reveals the prizes from third to first. The private draw
// and the stored results remain ordered from first to third.
const revealOrder = computed(() => [...props.prizes].reverse());

const currentPrize = computed<SimulationPrize | null>(
    () => revealOrder.value[currentIndex.value] ?? null,
);

// Before the first click we already show the first prize that will be drawn
// (the 3rd place), so the presenter can prepare the stage.
const displayPrize = computed<SimulationPrize | null>(
    () => currentPrize.value ?? revealOrder.value[0] ?? null,
);

const nextPrize = computed<SimulationPrize | null>(() => {
    if (currentIndex.value < 0) {
        return revealOrder.value[0] ?? null;
    }

    return revealOrder.value[currentIndex.value + 1] ?? null;
});

const placeLabel = (place: number | null, index: number) =>
    place ? `${place}º Prémio` : `${index + 1}º Prémio`;

const displayPlaceLabel = computed(() => {
    const prize = displayPrize.value;

    if (!prize) return null;

    return placeLabel(
        prize.place,
        props.prizes.findIndex((item) => item.id === prize.id),
    );
});

const primaryLabel = computed(() => {
    if (phase.value === "rolling") return "A sortear...";
    if (phase.value === "finished") return "Repetir apresentação";

    const next = nextPrize.value;

    if (!next) return "Apresentação concluída";

    return currentIndex.value < 0
        ? `Começar · ${placeLabel(next.place, props.prizes.indexOf(next))}`
        : `Próximo · ${placeLabel(next.place, props.prizes.indexOf(next))}`;
});

const primaryDisabled = computed(
    () => phase.value === "rolling" || revealOrder.value.length === 0,
);

const reelStyle = computed(() => ({
    transform: `translateY(${reelOffset.value}px)`,
    transition: reelTransition.value,
}));

const isRevealed = (prize: SimulationPrize) =>
    revealedIds.value.includes(prize.id);

const schedule = (callback: () => void, delay: number) => {
    const id = window.setTimeout(callback, delay);
    timers.push(id);

    return id;
};

const showConfetti = () => {
    const token = ++confettiToken;

    confettiVisible.value = true;
    confettiKey.value++;

    schedule(() => {
        if (token === confettiToken) {
            confettiVisible.value = false;
        }
    }, 3200);
};

const randomName = (pool: { name: string }[], fallback: string) =>
    pool.length > 0
        ? pool[Math.floor(Math.random() * pool.length)].name
        : fallback;

const buildReel = (prize: SimulationPrize) => {
    const winnerName = prize.winner?.name ?? "Sem elegíveis";
    const pool =
        prize.pool.length > 0
            ? prize.pool
            : [{ id: prize.winner?.id ?? 0, name: winnerName }];
    const items: string[] = [];
    const fillerCount = 48 + Math.floor(Math.random() * 12);

    for (let index = 0; index < fillerCount; index++) {
        items.push(randomName(pool, winnerName));
    }

    items.push(winnerName);
    items.push(randomName(pool, winnerName));

    reelItems.value = items;
    reelWinnerIndex.value = items.length - 2;
};

const startCurrentPrize = async () => {
    const prize = currentPrize.value;

    if (!prize) {
        phase.value = "finished";
        return;
    }

    const token = ++activeRun;

    if (!prize.winner) {
        revealedIds.value.push(prize.id);
        phase.value = nextPrize.value ? "revealed" : "finished";
        return;
    }

    buildReel(prize);
    reelOffset.value = 0;
    reelTransition.value = "none";
    phase.value = "rolling";

    await nextTick();

    if (token !== activeRun) return;

    // Give the browser one frame to render the reel at its starting position
    // before starting the long easing transition.
    await new Promise<void>((resolve) => schedule(resolve, 60));

    if (token !== activeRun) return;

    reelOffset.value = -(reelWinnerIndex.value - 1) * rowHeight;
    reelTransition.value = `transform ${reelDuration}ms cubic-bezier(0.12, 0.72, 0.14, 1)`;

    await new Promise<void>((resolve) => schedule(resolve, reelDuration + 100));

    if (token !== activeRun) return;

    revealedIds.value.push(prize.id);
    phase.value = nextPrize.value ? "revealed" : "finished";
    showConfetti();
};

const advance = () => {
    if (phase.value === "rolling" || revealOrder.value.length === 0) return;

    if (phase.value === "finished") {
        replay();
        return;
    }

    if (currentIndex.value < 0) {
        currentIndex.value = 0;
        void startCurrentPrize();
        return;
    }

    if (phase.value === "revealed" && nextPrize.value) {
        currentIndex.value++;
        void startCurrentPrize();
    }
};

const replay = () => {
    activeRun++;
    revealedIds.value = [];
    currentIndex.value = 0;
    phase.value = "idle";
    confettiVisible.value = false;
    reelItems.value = [];
    reelOffset.value = 0;
    reelTransition.value = "none";

    if (revealOrder.value.length === 0) {
        phase.value = "finished";
        return;
    }

    void startCurrentPrize();
};

const revealAll = () => {
    activeRun++;
    revealedIds.value = props.prizes.map((prize) => prize.id);
    currentIndex.value = revealOrder.value.length - 1;

    const lastPrize = revealOrder.value[currentIndex.value];

    if (lastPrize?.winner) {
        buildReel(lastPrize);
        reelOffset.value = -(reelWinnerIndex.value - 1) * rowHeight;
    } else {
        reelItems.value = [];
        reelOffset.value = 0;
    }

    reelTransition.value = "none";
    phase.value = "finished";
    showConfetti();
};

// Confetti pieces are generated once so they do not jump between re-renders.
const colors = [
    "#fbbf24",
    "#f472b6",
    "#38bdf8",
    "#a78bfa",
    "#34d399",
    "#f97316",
];

const confetti = Array.from({ length: 60 }, (_, index) => ({
    id: index,
    left: Math.random() * 100,
    drift: (Math.random() - 0.5) * 34,
    delay: Math.random() * 0.5,
    duration: 2.2 + Math.random() * 2,
    width: 5 + Math.random() * 7,
    height: 9 + Math.random() * 9,
    spin: Math.random() * 900 - 450,
    color: colors[index % colors.length],
}));

const confettiStyle = (piece: (typeof confetti)[number]) =>
    ({
        left: `${piece.left}%`,
        width: `${piece.width}px`,
        height: `${piece.height}px`,
        backgroundColor: piece.color,
        animationDelay: `${piece.delay}s`,
        animationDuration: `${piece.duration}s`,
        "--drift": `${piece.drift}vw`,
        "--spin": `${piece.spin}deg`,
    }) as Record<string, string>;

onBeforeUnmount(() => {
    activeRun++;
    confettiToken++;
    timers.forEach((id) => window.clearTimeout(id));
});
</script>

<template>
    <Head title="Sorteio Final — Apresentação" />

    <div
        class="relative flex min-h-screen w-full flex-col overflow-hidden bg-[#08090c] font-sans text-white antialiased selection:bg-white/20"
    >
        <div
            class="pointer-events-none absolute top-0 left-1/2 h-[36rem] w-[36rem] -translate-x-1/2 rounded-full bg-amber-500/[0.04] blur-3xl"
        ></div>

        <main
            class="relative z-10 flex min-h-screen w-full flex-col items-center justify-center gap-10 px-6 py-12 sm:px-10"
        >
            <div
                v-if="prizes.length === 0"
                class="text-center text-sm text-neutral-400"
            >
                Ainda não há prémios finais configurados para esta edição.
            </div>

            <template v-else>
                <div class="flex flex-col items-center gap-2 text-center">
                    <p
                        class="text-xs font-semibold tracking-[0.3em] text-amber-400 uppercase"
                    >
                        {{ displayPlaceLabel ?? "Sorteio Final" }}
                    </p>
                    <h1
                        class="max-w-4xl text-2xl font-black tracking-tight text-white sm:text-4xl"
                    >
                        {{ displayPrize?.name ?? "Sorteio Final" }}
                    </h1>
                </div>

                <div
                    class="grid w-full max-w-5xl grid-cols-1 items-center gap-8 sm:grid-cols-2"
                >
                    <div class="flex w-full items-center justify-center">
                        <div
                            class="flex aspect-square w-full max-w-sm items-center justify-center rounded-3xl border border-white/10 bg-white/[0.025] p-8"
                        >
                            <img
                                v-if="displayPrize?.image_url"
                                :src="displayPrize.image_url"
                                :alt="displayPrize.name"
                                class="h-full w-full object-contain drop-shadow-[0_20px_45px_rgba(0,0,0,0.5)]"
                            />
                            <span v-else class="text-sm text-neutral-500">
                                Sem imagem
                            </span>
                        </div>
                    </div>

                    <div class="flex w-full flex-col items-center gap-5">
                        <div
                            class="relative w-full overflow-hidden rounded-3xl border border-white/10 bg-white/[0.025]"
                            :style="{ height: `${rowHeight * 3}px` }"
                        >
                            <div
                                v-if="phase === 'idle'"
                                class="flex h-full flex-col items-center justify-center gap-2 text-neutral-500"
                            >
                                <span class="text-sm font-medium">
                                    Pronto para começar
                                </span>
                                <span class="text-xs text-neutral-600">
                                    3º → 2º → 1º
                                </span>
                            </div>

                            <div
                                v-else-if="!currentPrize?.winner"
                                class="flex h-full flex-col items-center justify-center gap-2 px-6 text-center text-neutral-500"
                            >
                                <span
                                    class="text-sm font-semibold text-neutral-300"
                                >
                                    Sem participantes elegíveis
                                </span>
                                <span class="text-xs">
                                    Confirma os scans da Sessão de Encerramento.
                                </span>
                            </div>

                            <template v-else>
                                <div
                                    class="pointer-events-none absolute inset-x-0 top-1/2 z-0 -translate-y-1/2 border-y border-white/10 bg-white/[0.03]"
                                    :style="{ height: `${rowHeight}px` }"
                                ></div>

                                <div
                                    class="reel-mask relative z-10 h-full overflow-hidden"
                                >
                                    <div :style="reelStyle">
                                        <div
                                            v-for="(name, index) in reelItems"
                                            :key="index"
                                            class="flex items-center justify-center px-4 text-center"
                                            :style="{
                                                height: `${rowHeight}px`,
                                            }"
                                        >
                                            <span
                                                class="max-w-full truncate text-xl font-bold sm:text-2xl"
                                                :class="
                                                    index === reelWinnerIndex &&
                                                    phase !== 'rolling'
                                                        ? 'text-amber-300'
                                                        : 'text-neutral-200'
                                                "
                                            >
                                                {{ name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                v-for="prize in revealOrder"
                                :key="prize.id"
                                class="h-1.5 w-1.5 rounded-full transition-colors duration-300"
                                :class="
                                    isRevealed(prize)
                                        ? 'bg-amber-400'
                                        : 'bg-white/20'
                                "
                            ></span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-full border px-7 py-3 text-sm font-semibold transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                        :class="
                            phase === 'finished'
                                ? 'border-white/10 bg-white/5 text-neutral-200 hover:bg-white/10'
                                : 'border-amber-400/30 bg-amber-400/15 text-amber-200 hover:bg-amber-400/25'
                        "
                        :disabled="primaryDisabled"
                        @click="advance"
                    >
                        <RotateCcw v-if="phase === 'finished'" :size="16" />
                        <Play
                            v-else-if="phase === 'idle' || currentIndex < 0"
                            :size="16"
                        />
                        <ArrowRight v-else :size="16" />
                        <span>{{ primaryLabel }}</span>
                    </button>

                    <div
                        class="flex items-center gap-3 text-[11px] text-neutral-500"
                    >
                        <span v-if="isSimulation">
                            Simulação — os nomes não são guardados.
                        </span>
                        <button
                            type="button"
                            class="cursor-pointer underline-offset-2 transition-colors hover:text-neutral-300 hover:underline"
                            @click="revealAll"
                        >
                            Revelar tudo
                        </button>
                    </div>
                </div>
            </template>
        </main>

        <div
            v-if="confettiVisible"
            :key="confettiKey"
            class="pointer-events-none fixed inset-0 z-20 overflow-hidden"
        >
            <span
                v-for="piece in confetti"
                :key="piece.id"
                class="confetti-piece"
                :style="confettiStyle(piece)"
            ></span>
        </div>
    </div>
</template>

<style scoped>
.reel-mask {
    -webkit-mask-image: linear-gradient(
        to bottom,
        transparent 0%,
        black 16%,
        black 84%,
        transparent 100%
    );
    mask-image: linear-gradient(
        to bottom,
        transparent 0%,
        black 16%,
        black 84%,
        transparent 100%
    );
}

@keyframes confetti-fall {
    0% {
        transform: translate3d(0, -12vh, 0) rotate(0deg);
        opacity: 1;
    }

    100% {
        transform: translate3d(var(--drift), 112vh, 0) rotate(var(--spin));
        opacity: 0;
    }
}

.confetti-piece {
    position: absolute;
    top: 0;
    border-radius: 2px;
    animation-name: confetti-fall;
    animation-timing-function: cubic-bezier(0.2, 0.6, 0.4, 1);
    animation-fill-mode: forwards;
}

@media (prefers-reduced-motion: reduce) {
    .confetti-piece {
        display: none;
    }
}
</style>
