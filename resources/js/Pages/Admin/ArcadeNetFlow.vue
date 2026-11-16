<script setup lang="ts">
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/UI/Modal.vue";
import PaginationLinks from "@/Components/UI/PaginationLinks.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import useSearch from "@/Composables/useSearch";
import type Paginated from "@/Types/Paginated";
import {
    ArrowRightLeft,
    CheckCircle2,
    Info,
    RotateCcw,
    Search,
    ShieldAlert,
    X,
} from "@lucide/vue";

type RiskLevel = "high" | "medium" | "low";
type ReviewState = "unreviewed" | "cleared" | "flagged";
type ReviewStatus = "open" | "cleared" | "flagged";

interface Account {
    enrollment_id: number;
    participant_id: number | null;
    user_id: number | null;
    name: string;
    email: string | null;
}

interface PairReview {
    status: ReviewStatus;
    note: string | null;
    reviewed_by: string | null;
    reviewed_at: string | null;
    reviewed_match_count: number;
    has_new_activity: boolean;
}

interface Pair {
    id: string;
    pair_key: string;
    low: Account;
    high: Account;
    matches: number;
    low_wins: number;
    high_wins: number;
    total_wagered: number;
    low_losses: number;
    high_losses: number;
    net_flow: number;
    flow_direction: "low_to_high" | "high_to_low" | "balanced";
    dominant: "low" | "high" | null;
    dominant_wins: number;
    dominant_share: number;
    risk_level: RiskLevel;
    risk_reasons: string[];
    first_match_at: string;
    last_match_at: string;
    review: PairReview | null;
    review_state: ReviewState;
}

interface Summary {
    pairs: number;
    suspicious: number;
    high_risk: number;
    net_flow: number;
    matches: number;
    unreviewed: number;
    risk_counts: Record<RiskLevel, number>;
    review_counts: Record<ReviewState, number>;
}

interface Filters {
    edition_id: string;
    query: string;
    risk: RiskLevel | "all";
    review_status: ReviewState | "all";
    sort: "net_flow" | "matches" | "dominant_share" | "last_match_at";
    direction: "asc" | "desc";
}

interface EditionOption {
    id: number;
    name: string;
    year: string | number;
}

interface Props {
    pairs: Paginated<Pair>;
    summary: Summary;
    filters: Filters;
    editions: EditionOption[];
}

const props = defineProps<Props>();

const search = useSearch("query");
const reviewModalOpen = ref(false);
const selectedPair = ref<Pair | null>(null);

const reviewForm = useForm({
    low_enrollment_id: 0,
    high_enrollment_id: 0,
    status: "cleared" as ReviewStatus,
    note: "",
});

const riskLabels: Record<RiskLevel, string> = {
    high: "Alto",
    medium: "Médio",
    low: "Baixo",
};

const reviewLabels: Record<ReviewState, string> = {
    unreviewed: "Por rever",
    cleared: "Revisto",
    flagged: "Sinalizado",
};

const reviewStatusLabels: Record<ReviewStatus, string> = {
    open: "Reaberta",
    cleared: "Revisto",
    flagged: "Sinalizado",
};

const riskItems = computed<PillOption[]>(() => [
    { id: "all", label: "Todos", count: props.summary.pairs },
    { id: "high", label: "Alto", count: props.summary.risk_counts.high },
    { id: "medium", label: "Médio", count: props.summary.risk_counts.medium },
    { id: "low", label: "Baixo", count: props.summary.risk_counts.low },
]);

const reviewItems = computed<PillOption[]>(() => [
    { id: "all", label: "Todos" },
    {
        id: "unreviewed",
        label: "Por rever",
        count: props.summary.review_counts.unreviewed,
    },
    {
        id: "cleared",
        label: "Revistos",
        count: props.summary.review_counts.cleared,
    },
    {
        id: "flagged",
        label: "Sinalizados",
        count: props.summary.review_counts.flagged,
    },
]);

const riskReasonLabels: Record<string, string> = {
    one_sided: "Resultado unilateral",
    high_volume: "Fluxo elevado",
    repeated_pair: "Muitas partidas entre o mesmo par",
};

const sortOptions = [
    { value: "net_flow", label: "Fluxo (maior)" },
    { value: "matches", label: "Partidas (mais)" },
    { value: "dominant_share", label: "Unilateralidade" },
    { value: "last_match_at", label: "Última partida" },
];

const formatPoints = (value: number) =>
    new Intl.NumberFormat("pt-PT").format(value);

const formatDate = (value: string | null) => {
    if (!value) return "—";

    return new Intl.DateTimeFormat("pt-PT", {
        dateStyle: "short",
        timeStyle: "short",
    }).format(new Date(value));
};

const initials = (name: string) =>
    name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? "")
        .join("");

const shortName = (name: string) => name.split(" ")[0] ?? name;

const flowLabel = (pair: Pair) => {
    if (pair.flow_direction === "low_to_high") {
        return `${shortName(pair.low.name)} → ${shortName(pair.high.name)}`;
    }

    if (pair.flow_direction === "high_to_low") {
        return `${shortName(pair.high.name)} → ${shortName(pair.low.name)}`;
    }

    return "Equilibrado";
};

const riskBadgeClass = (risk: RiskLevel) => {
    switch (risk) {
        case "high":
            return "border-red-500/30 bg-red-500/10 text-red-300";
        case "medium":
            return "border-amber-500/30 bg-amber-500/10 text-amber-300";
        default:
            return "border-white/10 bg-white/5 text-neutral-400";
    }
};

const reviewBadgeClass = (state: ReviewState) => {
    switch (state) {
        case "cleared":
            return "border-emerald-500/30 bg-emerald-500/10 text-emerald-300";
        case "flagged":
            return "border-red-500/30 bg-red-500/10 text-red-300";
        default:
            return "border-sky-500/30 bg-sky-500/10 text-sky-300";
    }
};

const updateFilter = (key: string, value: string | null) => {
    const url = new URL(window.location.href);

    if (value && value !== "all") {
        url.searchParams.set(key, value);
    } else {
        url.searchParams.delete(key);
    }

    url.searchParams.set("page", "1");

    router.visit(url.toString(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const onEditionChange = (event: Event) => {
    const value = (event.target as HTMLSelectElement).value;
    const url = new URL(window.location.href);

    // Unlike the other filters, an absent edition_id means "current edition"
    // on the backend. "all" must be sent explicitly.
    url.searchParams.set("edition_id", value);
    url.searchParams.set("page", "1");

    router.visit(url.toString(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const onSortChange = (event: Event) => {
    updateFilter("sort", (event.target as HTMLSelectElement).value);
};

const onRiskSelect = (item: PillOption) => {
    updateFilter("risk", item.id === "all" ? null : String(item.id));
};

const onReviewStatusSelect = (item: PillOption) => {
    updateFilter("review_status", item.id === "all" ? null : String(item.id));
};

const openReview = (pair: Pair) => {
    selectedPair.value = pair;
    reviewForm.low_enrollment_id = pair.low.enrollment_id;
    reviewForm.high_enrollment_id = pair.high.enrollment_id;
    reviewForm.status = "cleared";
    reviewForm.note = pair.review?.note ?? "";
    reviewForm.clearErrors();
    reviewModalOpen.value = true;
};

const closeReview = () => {
    reviewModalOpen.value = false;
    selectedPair.value = null;
    reviewForm.clearErrors();
};

const submitReview = (status: ReviewStatus) => {
    reviewForm.status = status;

    reviewForm.post(route("admin.arcadeNetFlow.review"), {
        preserveScroll: true,
        onSuccess: () => {
            closeReview();
            reviewForm.reset();
        },
    });
};
</script>

<template>
    <AdminLayout title="Fluxo entre Contas">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-1">
                <h1
                    class="text-xl font-bold tracking-tight text-white sm:text-2xl"
                >
                    Fluxo entre Contas
                </h1>
                <p class="max-w-3xl text-sm text-neutral-400">
                    Monitorização de transferências de pontos em partidas PvP. O
                    fluxo é a diferença entre os pontos que cada conta perdeu
                    para a outra; a margem da casa não é incluída.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-white/8 bg-black/50 p-5 backdrop-blur-md"
                >
                    <div class="text-2xl font-bold text-white sm:text-3xl">
                        {{ summary.pairs }}
                    </div>
                    <div
                        class="mt-1 font-mono text-[10px] tracking-wider text-neutral-500 uppercase"
                    >
                        Pares analisados
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-white/8 bg-black/50 p-5 backdrop-blur-md"
                >
                    <div class="text-2xl font-bold text-amber-300 sm:text-3xl">
                        {{ summary.suspicious }}
                    </div>
                    <div
                        class="mt-1 font-mono text-[10px] tracking-wider text-neutral-500 uppercase"
                    >
                        Suspeitos
                    </div>
                    <div class="mt-1 text-[11px] text-neutral-500">
                        {{ summary.high_risk }} de risco alto
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-white/8 bg-black/50 p-5 backdrop-blur-md"
                >
                    <div class="text-2xl font-bold text-white sm:text-3xl">
                        {{ formatPoints(summary.net_flow) }}
                    </div>
                    <div
                        class="mt-1 font-mono text-[10px] tracking-wider text-neutral-500 uppercase"
                    >
                        Fluxo entre pares
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-white/8 bg-black/50 p-5 backdrop-blur-md"
                >
                    <div class="text-2xl font-bold text-sky-300 sm:text-3xl">
                        {{ summary.unreviewed }}
                    </div>
                    <div
                        class="mt-1 font-mono text-[10px] tracking-wider text-neutral-500 uppercase"
                    >
                        Por rever
                    </div>
                    <div class="mt-1 text-[11px] text-neutral-500">
                        {{ summary.matches }} partidas no total
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/8 bg-black/40 p-4">
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex flex-1 flex-col gap-2 sm:flex-row">
                        <select
                            :value="filters.edition_id"
                            class="pill-container h-9 cursor-pointer px-3 text-xs text-white focus:outline-none"
                            aria-label="Filtrar por edição"
                            @change="onEditionChange"
                        >
                            <option value="all">Todas as edições</option>
                            <option
                                v-for="edition in editions"
                                :key="edition.id"
                                :value="String(edition.id)"
                            >
                                {{ edition.name }}
                            </option>
                        </select>

                        <div
                            class="relative flex flex-1 items-center sm:max-w-xs"
                        >
                            <Search
                                :size="15"
                                class="pointer-events-none absolute left-3 z-10 text-neutral-300"
                            />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Pesquisar nome ou email..."
                                class="pill-container h-9 w-full pr-8 pl-8.5 text-xs text-white placeholder-neutral-400 focus:outline-none"
                            />
                            <button
                                v-if="search"
                                type="button"
                                class="absolute right-2.5 z-10 cursor-pointer text-neutral-400 transition-colors hover:text-white"
                                aria-label="Limpar pesquisa"
                                @click="search = ''"
                            >
                                <X :size="13" />
                            </button>
                        </div>

                        <select
                            :value="filters.sort"
                            class="pill-container h-9 cursor-pointer px-3 text-xs text-white focus:outline-none"
                            aria-label="Ordenar pares"
                            @change="onSortChange"
                        >
                            <option
                                v-for="option in sortOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div
                        class="flex flex-col gap-2 xl:flex-row xl:items-center"
                    >
                        <PillSelector
                            :items="riskItems"
                            :model-value="filters.risk"
                            size="sm"
                            @select="onRiskSelect"
                        />
                        <PillSelector
                            :items="reviewItems"
                            :model-value="filters.review_status"
                            size="sm"
                            @select="onReviewStatusSelect"
                        />
                    </div>
                </div>

                <div
                    class="mt-3 flex items-start gap-2 rounded-xl border border-white/5 bg-white/[0.02] px-3 py-2 text-[11px] text-neutral-400"
                >
                    <Info :size="14" class="mt-0.5 shrink-0 text-sky-400" />
                    <span>
                        Um par é assinalado quando há pelo menos 3 partidas, o
                        mesmo lado vence pelo menos 75% delas e o fluxo é de
                        pelo menos 200 pts. Com 5 ou mais partidas, 80% ou mais
                        de vitórias e 500 pts ou mais de fluxo, o risco é alto.
                        Estes valores são indicadores, não provas de conluio.
                    </span>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-white/10 bg-black/40 shadow-[0_4px_24px_rgba(0,0,0,0.3)] backdrop-blur-md"
            >
                <div class="w-full overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-white/10 bg-white/[0.03]"
                            >
                                <th
                                    class="px-4 py-3 font-mono text-[10px] font-semibold tracking-wider text-neutral-400 uppercase first:pl-6"
                                >
                                    Par de contas
                                </th>
                                <th
                                    class="px-4 py-3 font-mono text-[10px] font-semibold tracking-wider text-neutral-400 uppercase"
                                >
                                    Partidas
                                </th>
                                <th
                                    class="px-4 py-3 font-mono text-[10px] font-semibold tracking-wider text-neutral-400 uppercase"
                                >
                                    Vitórias
                                </th>
                                <th
                                    class="px-4 py-3 font-mono text-[10px] font-semibold tracking-wider text-neutral-400 uppercase"
                                >
                                    Fluxo
                                </th>
                                <th
                                    class="px-4 py-3 font-mono text-[10px] font-semibold tracking-wider text-neutral-400 uppercase"
                                >
                                    Risco
                                </th>
                                <th
                                    class="px-4 py-3 font-mono text-[10px] font-semibold tracking-wider text-neutral-400 uppercase"
                                >
                                    Revisão
                                </th>
                                <th
                                    class="px-4 py-3 font-mono text-[10px] font-semibold tracking-wider text-neutral-400 uppercase"
                                >
                                    Última partida
                                </th>
                                <th
                                    class="px-4 py-3 text-right font-mono text-[10px] font-semibold tracking-wider text-neutral-400 uppercase last:pr-6"
                                >
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <tr
                                v-for="pair in pairs.data"
                                :key="pair.pair_key"
                                class="align-top transition-colors hover:bg-white/[0.02]"
                            >
                                <td class="px-4 py-4 first:pl-6">
                                    <div class="flex flex-col gap-2">
                                        <div
                                            v-for="account in [
                                                pair.low,
                                                pair.high,
                                            ]"
                                            :key="account.enrollment_id"
                                            class="flex items-center gap-2.5"
                                        >
                                            <div
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-[10px] font-semibold text-neutral-300"
                                            >
                                                {{ initials(account.name) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div
                                                    class="truncate text-sm font-medium text-white"
                                                >
                                                    {{ account.name }}
                                                </div>
                                                <div
                                                    class="truncate text-[11px] text-neutral-500"
                                                >
                                                    {{ account.email ?? "—" }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="font-semibold text-white">
                                        {{ pair.matches }}
                                    </div>
                                    <div class="text-[11px] text-neutral-500">
                                        partidas
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="font-semibold text-white">
                                        {{ pair.low_wins }}
                                        <span class="text-neutral-600">/</span>
                                        {{ pair.high_wins }}
                                    </div>
                                    <div class="text-[11px] text-neutral-500">
                                        {{ shortName(pair.low.name) }} vs
                                        {{ shortName(pair.high.name) }}
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div
                                        class="flex items-center gap-1.5 font-semibold"
                                        :class="
                                            pair.net_flow > 0
                                                ? 'text-amber-300'
                                                : 'text-neutral-300'
                                        "
                                    >
                                        <ArrowRightLeft :size="13" />
                                        {{ formatPoints(pair.net_flow) }} pts
                                    </div>
                                    <div class="text-[11px] text-neutral-500">
                                        {{ flowLabel(pair) }}
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                                        :class="riskBadgeClass(pair.risk_level)"
                                    >
                                        <ShieldAlert
                                            v-if="pair.risk_level !== 'low'"
                                            :size="11"
                                        />
                                        {{ riskLabels[pair.risk_level] }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                                        :class="
                                            reviewBadgeClass(pair.review_state)
                                        "
                                    >
                                        <CheckCircle2
                                            v-if="
                                                pair.review_state === 'cleared'
                                            "
                                            :size="11"
                                        />
                                        {{ reviewLabels[pair.review_state] }}
                                    </span>
                                    <div
                                        v-if="
                                            pair.review?.has_new_activity &&
                                            pair.review.status !== 'open'
                                        "
                                        class="mt-1 text-[10px] text-amber-400"
                                    >
                                        Novo movimento desde a revisão
                                    </div>
                                </td>

                                <td
                                    class="px-4 py-4 text-xs whitespace-nowrap text-neutral-400"
                                >
                                    {{ formatDate(pair.last_match_at) }}
                                </td>

                                <td class="px-4 py-4 text-right last:pr-6">
                                    <button
                                        type="button"
                                        class="pill-item cursor-pointer px-3 py-1.5 text-xs font-medium text-neutral-300 transition-colors hover:text-white"
                                        @click="openReview(pair)"
                                    >
                                        Rever
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="pairs.data.length === 0">
                                <td :colspan="8" class="px-6 py-16 text-center">
                                    <div
                                        class="flex flex-col items-center justify-center gap-2"
                                    >
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-neutral-400"
                                        >
                                            <ArrowRightLeft :size="24" />
                                        </div>
                                        <span
                                            class="text-sm font-medium text-white"
                                        >
                                            Nenhum par encontrado
                                        </span>
                                        <span class="text-xs text-neutral-400">
                                            Ainda não existem partidas PvP
                                            resolvidas para estes filtros.
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <PaginationLinks :links="pairs.links" />
        </div>

        <Modal
            v-model="reviewModalOpen"
            title="Rever par de contas"
            description="Confirma o resultado da análise. A nota fica registada para consulta futura."
            max-width="2xl"
        >
            <div v-if="selectedPair" class="flex flex-col gap-5">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div
                        v-for="account in [selectedPair.low, selectedPair.high]"
                        :key="account.enrollment_id"
                        class="rounded-2xl border border-white/8 bg-white/[0.02] p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-xs font-semibold text-neutral-300"
                            >
                                {{ initials(account.name) }}
                            </div>
                            <div class="min-w-0">
                                <div
                                    class="truncate text-sm font-semibold text-white"
                                >
                                    {{ account.name }}
                                </div>
                                <div
                                    class="truncate text-[11px] text-neutral-500"
                                >
                                    {{ account.email ?? "—" }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div
                        class="rounded-xl border border-white/8 bg-white/[0.02] p-3"
                    >
                        <div class="text-lg font-bold text-white">
                            {{ selectedPair.matches }}
                        </div>
                        <div class="text-[10px] text-neutral-500 uppercase">
                            Partidas
                        </div>
                    </div>
                    <div
                        class="rounded-xl border border-white/8 bg-white/[0.02] p-3"
                    >
                        <div class="text-lg font-bold text-white">
                            {{ selectedPair.low_wins }} /
                            {{ selectedPair.high_wins }}
                        </div>
                        <div class="text-[10px] text-neutral-500 uppercase">
                            Vitórias
                        </div>
                    </div>
                    <div
                        class="rounded-xl border border-white/8 bg-white/[0.02] p-3"
                    >
                        <div class="text-lg font-bold text-amber-300">
                            {{ formatPoints(selectedPair.net_flow) }} pts
                        </div>
                        <div class="text-[10px] text-neutral-500 uppercase">
                            Fluxo
                        </div>
                        <div class="mt-0.5 text-[10px] text-neutral-500">
                            {{ flowLabel(selectedPair) }}
                        </div>
                    </div>
                    <div
                        class="rounded-xl border border-white/8 bg-white/[0.02] p-3"
                    >
                        <div class="text-lg font-bold text-white">
                            {{ riskLabels[selectedPair.risk_level] }}
                        </div>
                        <div class="text-[10px] text-neutral-500 uppercase">
                            Risco
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <span
                        v-for="reason in selectedPair.risk_reasons"
                        :key="reason"
                        class="rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[11px] text-neutral-300"
                    >
                        {{ riskReasonLabels[reason] ?? reason }}
                    </span>
                </div>

                <div
                    v-if="selectedPair.review"
                    class="rounded-xl border border-white/8 bg-white/[0.02] p-3 text-[11px] text-neutral-400"
                >
                    <div>
                        Última revisão:
                        {{ reviewStatusLabels[selectedPair.review.status] }}
                        <span v-if="selectedPair.review.reviewed_by">
                            por {{ selectedPair.review.reviewed_by }}
                        </span>
                        <span v-if="selectedPair.review.reviewed_at">
                            em {{ formatDate(selectedPair.review.reviewed_at) }}
                        </span>
                    </div>
                    <div v-if="selectedPair.review.note" class="mt-1">
                        Nota: {{ selectedPair.review.note }}
                    </div>
                </div>

                <label class="flex flex-col gap-1.5 text-xs text-neutral-300">
                    Nota de revisão (opcional)
                    <textarea
                        v-model="reviewForm.note"
                        rows="3"
                        maxlength="1000"
                        placeholder="Ex.: confirmado com os participantes, sem indícios de conluio..."
                        class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-white placeholder-neutral-500 focus:border-white/20 focus:outline-none"
                    />
                </label>

                <p
                    v-if="
                        reviewForm.errors.note ||
                        reviewForm.errors.status ||
                        reviewForm.errors.low_enrollment_id ||
                        reviewForm.errors.high_enrollment_id
                    "
                    class="text-xs text-red-400"
                >
                    {{
                        reviewForm.errors.note ??
                        reviewForm.errors.status ??
                        reviewForm.errors.low_enrollment_id ??
                        reviewForm.errors.high_enrollment_id
                    }}
                </p>
            </div>

            <template #footer>
                <button
                    type="button"
                    class="cursor-pointer px-2 text-xs text-neutral-400 transition-colors hover:text-white"
                    @click="closeReview"
                >
                    Cancelar
                </button>

                <button
                    v-if="selectedPair?.review"
                    type="button"
                    class="flex cursor-pointer items-center gap-1.5 px-2 text-xs text-neutral-400 transition-colors hover:text-white"
                    :disabled="reviewForm.processing"
                    @click="submitReview('open')"
                >
                    <RotateCcw :size="13" />
                    Reabrir
                </button>

                <PrimaryButton
                    type="button"
                    color="danger"
                    text-size="text-xs"
                    padding="px-4 py-2"
                    :disabled="reviewForm.processing"
                    @click="submitReview('flagged')"
                >
                    <ShieldAlert :size="14" />
                    Sinalizar
                </PrimaryButton>

                <PrimaryButton
                    type="button"
                    color="pill"
                    text-size="text-xs"
                    padding="px-4 py-2"
                    :disabled="reviewForm.processing"
                    @click="submitReview('cleared')"
                >
                    <CheckCircle2 :size="14" />
                    Marcar como revisto
                </PrimaryButton>
            </template>
        </Modal>
    </AdminLayout>
</template>
