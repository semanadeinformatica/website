<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from "vue";
import { Head } from "@inertiajs/vue3";
import {
    ArrowRight,
    Play,
    RotateCcw,
    SkipForward,
    Sparkles,
    Trophy,
} from "@lucide/vue";
import type { SimulationPrize } from "@/Types/Raffle";

interface Props {
    edition: {
        id: number;
        name: string;
        year: number;
    };
    closingEventName: string | null;
    prizes: SimulationPrize[];
    isSimulation: boolean;
}

const props = defineProps<Props>();

type Phase = "idle" | "rolling" | "revealed" | "finished";

const phase = ref<Phase>("idle");
const currentIndex = ref(-1);
const rollingName = ref("");
const revealedIds = ref<number[]>([]);
const confettiKey = ref(0);
const confettiVisible = ref(false);

let activeRun = 0;
let confettiToken = 0;
const timers: number[] = [];

// The presentation reveals the prizes from third to first. The private draw
// and the stored results remain ordered from first to third.
const revealOrder = computed(() => [...props.prizes].reverse());

const currentPrize = computed<SimulationPrize | null>(
    () => revealOrder.value[currentIndex.value] ?? null,
);

const nextPrize = computed<SimulationPrize | null>(() => {
    if (currentIndex.value < 0) {
        return revealOrder.value[0] ?? null;
    }

    return revealOrder.value[currentIndex.value + 1] ?? null;
});

const progress = computed(() => {
    if (props.prizes.length === 0) return 0;

    return (revealedIds.value.length / props.prizes.length) * 100;
});

const allPrizesResolved = computed(
    () =>
        props.prizes.length > 0 &&
        props.prizes.every((prize) => prize.winner !== null),
);

const badgeStyle = computed(() => {
    if (props.isSimulation) {
        return "border-amber-400/30 bg-amber-400/10 text-amber-300";
    }

    return allPrizesResolved.value
        ? "border-emerald-400/30 bg-emerald-400/10 text-emerald-300"
        : "border-white/10 bg-white/5 text-neutral-300";
});

const badgeLabel = computed(() => {
    if (props.isSimulation) return "Simulação";

    return allPrizesResolved.value
        ? "Resultados oficiais"
        : "Resultados parciais";
});

const placeLabel = (place: number | null, index: number) =>
    place ? `${place}º Prémio` : `${index + 1}º Prémio`;

const currentPlaceLabel = computed(() => {
    const prize = currentPrize.value;

    if (!prize) return null;

    return placeLabel(
        prize.place,
        props.prizes.findIndex((item) => item.id === prize.id),
    );
});

const nextActionLabel = computed(() => {
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

const placeAccent = (place: number | null) => {
    if (place === 1)
        return "border-amber-400/40 shadow-[0_0_40px_rgba(251,191,36,0.18)]";
    if (place === 2)
        return "border-slate-300/30 shadow-[0_0_36px_rgba(203,213,225,0.12)]";
    return "border-amber-700/40 shadow-[0_0_36px_rgba(180,83,9,0.14)]";
};

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

const isRevealed = (prize: SimulationPrize) =>
    revealedIds.value.includes(prize.id);

const startCurrentPrize = async () => {
    const prize = currentPrize.value;

    if (!prize) {
        phase.value = "finished";
        return;
    }

    const token = ++activeRun;

    if (!prize.winner) {
        revealedIds.value.push(prize.id);
        rollingName.value = "";
        phase.value = nextPrize.value ? "revealed" : "finished";
        return;
    }

    phase.value = "rolling";

    const pool = prize.pool.length > 0 ? prize.pool : [prize.winner];
    const duration = 2600;
    const startedAt = performance.now();

    await new Promise<void>((resolve) => {
        const tick = () => {
            if (token !== activeRun) {
                resolve();
                return;
            }

            const elapsed = performance.now() - startedAt;

            if (elapsed >= duration) {
                rollingName.value = prize.winner!.name;
                resolve();
                return;
            }

            const ratio = Math.min(1, elapsed / duration);

            rollingName.value =
                pool[Math.floor(Math.random() * pool.length)].name;

            schedule(tick, 45 + Math.pow(ratio, 3) * 260);
        };

        tick();
    });

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
    rollingName.value = "";
    confettiVisible.value = false;
    phase.value = "idle";

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
    rollingName.value =
        revealOrder.value[currentIndex.value]?.winner?.name ?? "";
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

const confetti = Array.from({ length: 70 }, (_, index) => ({
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
        class="relative flex min-h-screen w-full flex-col overflow-hidden bg-[#07080b] font-sans text-white antialiased selection:bg-white/20"
    >
        <div
            class="bg-sinf-primary/20 pointer-events-none absolute -top-48 left-1/2 h-[42rem] w-[42rem] -translate-x-1/2 rounded-full blur-3xl"
        ></div>
        <div
            class="bg-sinf-secondary/15 pointer-events-none absolute -bottom-56 -left-40 h-[34rem] w-[34rem] rounded-full blur-3xl"
        ></div>
        <div
            class="pointer-events-none absolute top-1/3 -right-40 h-[30rem] w-[30rem] rounded-full bg-amber-500/10 blur-3xl"
        ></div>

        <header
            class="relative z-10 flex flex-col gap-4 px-6 pt-6 sm:flex-row sm:items-center sm:justify-between sm:px-10 lg:px-14"
        >
            <div class="flex flex-col gap-1">
                <p
                    class="text-[11px] font-semibold tracking-[0.35em] text-neutral-500 uppercase"
                >
                    SINF {{ edition.year }}
                </p>
                <h1
                    class="text-2xl font-black tracking-tight text-white sm:text-3xl"
                >
                    Sorteio Final
                </h1>
                <p class="text-xs text-neutral-400">
                    {{
                        closingEventName ??
                        "Sessão de Encerramento não encontrada"
                    }}
                </p>
            </div>

            <div
                class="inline-flex items-center gap-2 self-start rounded-full border px-3.5 py-1.5 text-xs font-semibold sm:self-auto"
                :class="badgeStyle"
            >
                <Sparkles v-if="isSimulation" :size="14" />
                <Trophy v-else :size="14" />
                <span>{{ badgeLabel }}</span>
            </div>
        </header>

        <main
            class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 py-10 sm:px-10 lg:px-14"
        >
            <div
                v-if="prizes.length === 0"
                class="flex flex-col items-center gap-3 text-center"
            >
                <Trophy :size="28" class="text-neutral-600" />
                <p class="text-sm font-medium text-neutral-300">
                    Ainda não há prémios finais configurados para esta edição.
                </p>
            </div>

            <div
                v-else
                class="flex w-full max-w-6xl flex-col items-center gap-10"
            >
                <section class="flex flex-col items-center gap-4 text-center">
                    <div
                        class="flex items-center gap-2 text-xs font-medium tracking-[0.25em] text-neutral-500 uppercase"
                    >
                        <span v-if="currentPlaceLabel">
                            {{ currentPlaceLabel }}
                        </span>
                        <span v-else>3º → 2º → 1º</span>
                    </div>

                    <h2
                        class="max-w-4xl text-xl font-bold tracking-tight text-white sm:text-2xl lg:text-3xl"
                    >
                        {{ currentPrize?.name ?? "Sorteio Final" }}
                    </h2>

                    <div
                        class="relative flex min-h-40 w-full max-w-4xl items-center justify-center overflow-hidden rounded-3xl border border-white/10 bg-white/[0.025] px-6 py-10 shadow-[0_20px_80px_rgba(0,0,0,0.45)] backdrop-blur-md sm:min-h-52"
                    >
                        <div
                            class="pointer-events-none absolute inset-x-10 top-0 h-px bg-linear-to-r from-transparent via-white/30 to-transparent"
                        ></div>

                        <div
                            v-if="phase === 'idle'"
                            class="flex flex-col items-center gap-2 text-neutral-500"
                        >
                            <Sparkles :size="22" class="animate-pulse" />
                            <span
                                class="text-xs font-medium tracking-[0.3em] uppercase"
                            >
                                Pronto para começar
                            </span>
                            <span class="text-xs text-neutral-600">
                                O apresentador controla o ritmo de cada prémio.
                            </span>
                        </div>

                        <div
                            v-else-if="currentPrize && !currentPrize.winner"
                            class="flex flex-col items-center gap-2 text-neutral-500"
                        >
                            <span
                                class="text-lg font-semibold text-neutral-300"
                            >
                                Sem participantes elegíveis
                            </span>
                            <span class="text-xs">
                                Confirma os scans da Sessão de Encerramento.
                            </span>
                        </div>

                        <span
                            v-else-if="phase === 'rolling'"
                            class="max-w-full truncate bg-linear-to-r from-white via-amber-200 to-white bg-clip-text px-2 text-3xl font-black tracking-tight text-transparent sm:text-5xl lg:text-6xl"
                        >
                            {{ rollingName }}
                        </span>

                        <div
                            v-else
                            :key="`winner-${currentPrize?.id}-${confettiKey}`"
                            class="winner-pop flex flex-col items-center gap-2"
                        >
                            <span
                                class="text-[11px] font-semibold tracking-[0.35em] text-amber-400 uppercase"
                            >
                                Vencedor
                            </span>
                            <span
                                class="max-w-full text-center text-3xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl"
                            >
                                {{ currentPrize?.winner?.name ?? "—" }}
                            </span>
                        </div>
                    </div>

                    <p
                        v-if="currentPrize?.description"
                        class="max-w-2xl text-sm text-neutral-400"
                    >
                        {{ currentPrize.description }}
                    </p>

                    <div
                        class="mt-1 h-1 w-full max-w-xl overflow-hidden rounded-full bg-white/10"
                    >
                        <div
                            class="from-sinf-primary h-full rounded-full bg-linear-to-r to-amber-400 transition-all duration-700"
                            :style="{ width: `${progress}%` }"
                        ></div>
                    </div>

                    <div class="flex flex-col items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-full border px-6 py-3 text-sm font-semibold transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
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
                            <span>{{ nextActionLabel }}</span>
                        </button>
                        <span class="text-[11px] text-neutral-600">
                            Cada clique revela o prémio seguinte.
                        </span>
                    </div>
                </section>

                <section class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3">
                    <article
                        v-for="(prize, index) in prizes"
                        :key="prize.id"
                        class="flex flex-col gap-3 rounded-2xl border bg-white/[0.025] p-4 backdrop-blur-sm transition-all duration-500"
                        :class="[
                            placeAccent(prize.place),
                            isRevealed(prize)
                                ? 'opacity-100'
                                : 'opacity-50 grayscale',
                        ]"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-neutral-300">
                                {{ placeLabel(prize.place, index) }}
                            </span>
                            <span
                                v-if="prize.is_simulated"
                                class="rounded-full border border-amber-400/20 bg-amber-400/10 px-2 py-0.5 text-[10px] font-semibold text-amber-300"
                            >
                                Simulação
                            </span>
                            <Trophy v-else :size="14" class="text-amber-400" />
                        </div>

                        <p class="line-clamp-2 text-xs text-neutral-400">
                            {{ prize.name }}
                        </p>

                        <div class="mt-auto min-h-7">
                            <span
                                v-if="isRevealed(prize)"
                                class="text-base font-bold text-white"
                            >
                                {{ prize.winner?.name ?? "Sem elegíveis" }}
                            </span>
                            <span v-else class="text-xs text-neutral-600">
                                Por revelar
                            </span>
                        </div>
                    </article>
                </section>
            </div>
        </main>

        <footer
            class="relative z-10 flex flex-col gap-3 border-t border-white/5 px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-10 lg:px-14"
        >
            <p class="text-xs text-neutral-500">
                <template v-if="isSimulation">
                    <span class="font-semibold text-amber-400">Simulação:</span>
                    os nomes apresentados não foram guardados. Usa “Sortear 1º →
                    3º” no painel para realizar o sorteio oficial.
                </template>
                <template v-else-if="allPrizesResolved">
                    Resultado oficial do sorteio final da SINF
                    {{ edition.year }}.
                </template>
                <template v-else>
                    Resultado parcial: alguns prémios ainda não foram sorteados.
                </template>
            </p>

            <div
                v-if="prizes.length > 0"
                class="flex flex-wrap items-center gap-2"
            >
                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-medium text-neutral-300 transition-colors hover:bg-white/10 hover:text-white"
                    @click="revealAll"
                >
                    <SkipForward :size="14" />
                    <span>Revelar tudo</span>
                </button>
                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-medium text-neutral-300 transition-colors hover:bg-white/10 hover:text-white"
                    @click="replay"
                >
                    <RotateCcw :size="14" />
                    <span>Repetir</span>
                </button>
            </div>
        </footer>

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

@keyframes winner-pop {
    0% {
        transform: scale(0.86);
        opacity: 0;
        filter: blur(10px);
    }

    60% {
        transform: scale(1.04);
    }

    100% {
        transform: scale(1);
        opacity: 1;
        filter: blur(0);
    }
}

.winner-pop {
    animation: winner-pop 0.65s ease-out both;
}

@media (prefers-reduced-motion: reduce) {
    .confetti-piece {
        display: none;
    }

    .winner-pop {
        animation: none;
    }
}
</style>
