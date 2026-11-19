<script setup lang="ts">
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import type RaffleDraw from "@/Types/Raffle";
import type { EligibleParticipant } from "@/Types/Raffle";
import type Edition from "@/Types/Edition";
import type Paginated from "@/Types/Paginated";
import {
    AlertCircle,
    Dices,
    Inbox,
    RotateCcw,
    Search,
    Sparkles,
    Trophy,
    X,
} from "@lucide/vue";

interface Props {
    items: Paginated<RaffleDraw>;
    with: {
        editions: Edition[];
    };
    isSearchable?: boolean;
    eligibleCount?: number;
    totalEntries?: number;
    eligibleParticipants?: EligibleParticipant[];
    selectedDrawId?: number | null;
    selectedDrawName?: string | null;
    requiredEventName?: string | null;
    hasClosingEvent?: boolean;
    activeEditionId?: number;
}

const props = withDefaults(defineProps<Props>(), {
    isSearchable: false,
    eligibleCount: 0,
    totalEntries: 0,
    eligibleParticipants: () => [],
    selectedDrawId: null,
    selectedDrawName: null,
    requiredEventName: null,
    hasClosingEvent: true,
    activeEditionId: 0,
});

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);

const sortedDraws = computed(() =>
    [...props.items.data].sort(
        (first, second) => (first.place ?? 99) - (second.place ?? 99),
    ),
);

const pendingDraws = computed(() =>
    sortedDraws.value.filter((draw) => !draw.winner_enrollment_id),
);

const drawnDraws = computed(() =>
    sortedDraws.value.filter((draw) => Boolean(draw.winner_enrollment_id)),
);

const hasDraws = computed(() => props.items.data.length > 0);
const allDrawn = computed(
    () => hasDraws.value && pendingDraws.value.length === 0,
);
const canDraw = computed(
    () => hasDraws.value && props.hasClosingEvent && !allDrawn.value,
);

const simulateUrl = computed(() =>
    route("admin.raffleDraws.simulate", {
        edition_id: props.activeEditionId,
    }),
);

const selectDraw = (drawId: string) => {
    const targetDraw = props.items.data.find(
        (draw) => draw.id.toString() === drawId.toString(),
    );
    const editionId = targetDraw?.edition_id ?? props.activeEditionId;

    router.get(
        route("admin.raffleDraws.index"),
        {
            edition_id: editionId,
            draw_id: drawId,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: [
                "items",
                "eligibleParticipants",
                "eligibleCount",
                "totalEntries",
                "selectedDrawId",
                "selectedDrawName",
                "requiredEventName",
                "hasClosingEvent",
                "activeEditionId",
            ],
        },
    );
};

// Search & filter eligible participants
const eligibleSearch = ref("");
const filteredEligible = computed(() => {
    const query = eligibleSearch.value.trim().toLowerCase();

    if (!query) {
        return props.eligibleParticipants;
    }

    return props.eligibleParticipants.filter(
        (participant) =>
            participant.name.toLowerCase().includes(query) ||
            (participant.email &&
                participant.email.toLowerCase().includes(query)),
    );
});

const formatProbability = (points: number) => {
    if (!props.totalEntries || props.totalEntries <= 0) return "0.0%";

    const percentage = (points / props.totalEntries) * 100;

    return percentage < 0.1 ? "<0.1%" : `${percentage.toFixed(1)}%`;
};

const formatPlace = (place: number | null) => (place ? `${place}º` : "—");

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return "-";

    const date = new Date(dateStr);

    return date.toLocaleString("pt-PT", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Draw all prizes (first to third)
const showDrawAllModal = ref(false);
const isDrawingAll = ref(false);

const confirmDrawAll = () => {
    if (!canDraw.value) return;

    isDrawingAll.value = true;

    router.post(
        route("admin.raffleDraws.draw-all"),
        { edition_id: props.activeEditionId },
        {
            preserveScroll: true,
            onFinish: () => {
                isDrawingAll.value = false;
                showDrawAllModal.value = false;
            },
        },
    );
};

// Reset all prizes
const showResetAllModal = ref(false);
const isResettingAll = ref(false);

const confirmResetAll = () => {
    isResettingAll.value = true;

    router.post(
        route("admin.raffleDraws.reset-all"),
        { edition_id: props.activeEditionId },
        {
            preserveScroll: true,
            onFinish: () => {
                isResettingAll.value = false;
                showResetAllModal.value = false;
            },
        },
    );
};
</script>

<template>
    <CRUDView
        title="Sorteio Final"
        view="RaffleDraws"
        :items="items"
        name="raffleDraws"
        :is-searchable="isSearchable"
    >
        <template #heading>
            <h1
                class="text-base font-bold tracking-tight text-white sm:text-lg"
            >
                Sorteio Final
            </h1>
        </template>

        <template #top>
            <section
                class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-black/40 p-5 shadow-[0_4px_24px_rgba(0,0,0,0.3)] backdrop-blur-md"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center gap-2">
                            <Trophy :size="16" class="text-amber-400" />
                            <h2 class="text-sm font-semibold text-white">
                                Sorteio privado
                            </h2>
                            <span
                                class="rounded-full border border-white/10 bg-white/5 px-2 py-0.5 font-mono text-[11px] text-neutral-300"
                            >
                                {{ drawnDraws.length }}/{{ items.data.length }}
                                sorteados
                            </span>
                        </div>
                        <p class="max-w-2xl text-xs text-neutral-400">
                            Sorteia os prémios por ordem, do 1º ao 3º lugar.
                            Apenas participam inscritos que fizeram scan na
                            <span class="font-medium text-neutral-200">
                                {{
                                    requiredEventName ??
                                    "Sessão de Encerramento"
                                }} </span
                            >. Staff e administração ficam sempre de fora. Quem
                            já venceu um prémio não volta a entrar.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <PrimaryButton
                            color="primary"
                            padding="px-4 py-2"
                            text-size="text-xs"
                            :disabled="!canDraw || isDrawingAll"
                            @click="showDrawAllModal = true"
                        >
                            <Dices :size="15" />
                            <span>Sortear 1º → 3º</span>
                        </PrimaryButton>

                        <PrimaryButton
                            :href="simulateUrl"
                            external
                            target="_blank"
                            rel="noopener noreferrer"
                            color="pill"
                            padding="px-4 py-2"
                            text-size="text-xs"
                        >
                            <Sparkles :size="15" />
                            <span>Simular sorteio</span>
                        </PrimaryButton>

                        <PrimaryButton
                            color="danger"
                            padding="px-4 py-2"
                            text-size="text-xs"
                            :disabled="
                                drawnDraws.length === 0 || isResettingAll
                            "
                            @click="showResetAllModal = true"
                        >
                            <RotateCcw :size="14" />
                            <span>Repor sorteio</span>
                        </PrimaryButton>
                    </div>
                </div>

                <div
                    v-if="!hasClosingEvent"
                    class="flex items-start gap-2 rounded-xl border border-red-400/20 bg-red-400/5 p-3 text-xs text-red-300"
                >
                    <AlertCircle :size="15" class="mt-0.5 shrink-0" />
                    <span>
                        Não foi encontrada uma Sessão de Encerramento para esta
                        edição. Cria o evento (ou confirma o nome/tópico) antes
                        de sortear.
                    </span>
                </div>

                <div
                    v-else-if="allDrawn"
                    class="flex items-center gap-2 rounded-xl border border-emerald-400/20 bg-emerald-400/5 p-3 text-xs text-emerald-300"
                >
                    <Trophy :size="15" class="shrink-0" />
                    <span>
                        Todos os prémios foram sorteados. A apresentação pode
                        ser repetida em “Simular sorteio”; usa “Repor sorteio”
                        para voltar a sortear.
                    </span>
                </div>
            </section>
        </template>

        <template #header>
            <HeaderRow>
                <Header
                    filter-by="edition_id"
                    :filter-values="editions"
                    :reload-props="[
                        'items',
                        'eligibleParticipants',
                        'eligibleCount',
                        'totalEntries',
                        'selectedDrawId',
                        'selectedDrawName',
                        'requiredEventName',
                        'hasClosingEvent',
                        'activeEditionId',
                    ]"
                >
                    Edição
                </Header>
                <Header sort-by="place">Posição</Header>
                <Header sort-by="name">Prémio</Header>
                <Header>Presença Obrigatória</Header>
                <Header sort-by="is_active">Estado</Header>
                <Header>Vencedor</Header>
                <Header sort-by="draw_at">Data</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="raffleDraws" :item="item">
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>

                <Cell>
                    <span
                        class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border px-2 text-xs font-bold"
                        :class="
                            item.place === 1
                                ? 'border-amber-400/30 bg-amber-400/10 text-amber-300'
                                : item.place === 2
                                  ? 'border-slate-300/30 bg-slate-300/10 text-slate-200'
                                  : 'border-amber-700/30 bg-amber-700/10 text-amber-500'
                        "
                    >
                        {{ formatPlace(item.place) }}
                    </span>
                </Cell>

                <Cell>
                    <div class="flex flex-col">
                        <span class="font-medium text-white">{{
                            item.name
                        }}</span>
                        <span
                            v-if="item.description"
                            class="line-clamp-1 text-xs text-neutral-400"
                        >
                            {{ item.description }}
                        </span>
                    </div>
                </Cell>

                <Cell class="text-xs">
                    <span
                        v-if="requiredEventName"
                        class="inline-flex items-center gap-1.5 text-neutral-300"
                    >
                        <span
                            class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"
                        ></span>
                        <span class="line-clamp-1">
                            {{ requiredEventName }}
                        </span>
                    </span>
                    <span v-else class="text-red-400"> Sem evento </span>
                </Cell>

                <Cell class="whitespace-nowrap">
                    <span
                        class="text-xs font-medium"
                        :class="
                            item.winner_enrollment_id
                                ? 'text-purple-400'
                                : item.is_active
                                  ? 'text-emerald-400'
                                  : 'text-neutral-500'
                        "
                    >
                        {{
                            item.winner_enrollment_id
                                ? "Sorteado"
                                : item.is_active
                                  ? "Por sortear"
                                  : "Inativo"
                        }}
                    </span>
                </Cell>

                <Cell>
                    <div
                        v-if="item.winner?.participant?.user?.name"
                        class="flex flex-col"
                    >
                        <span class="text-xs font-semibold text-amber-400">
                            {{ item.winner.participant.user.name }}
                        </span>
                        <span
                            v-if="item.winner.participant.user.email"
                            class="text-[11px] text-neutral-400"
                        >
                            {{ item.winner.participant.user.email }}
                        </span>
                    </div>
                    <span v-else class="text-xs text-neutral-500"> — </span>
                </Cell>

                <Cell class="text-xs whitespace-nowrap text-neutral-400">
                    {{ formatDate(item.draw_at) }}
                </Cell>
            </Row>
        </template>

        <template #bottom>
            <div
                class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-black/40 p-5 shadow-[0_4px_24px_rgba(0,0,0,0.3)] backdrop-blur-md"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-semibold text-white">
                                Participantes elegíveis
                            </h2>
                            <span class="font-mono text-xs text-neutral-400">
                                ({{ eligibleCount }} participantes)
                            </span>
                        </div>
                        <p class="text-xs text-neutral-400">
                            A avaliar:
                            <span class="font-medium text-amber-400">{{
                                selectedDrawName ?? "Sorteio"
                            }}</span>
                            · Presença obrigatória:
                            <span class="font-medium text-neutral-200">
                                {{
                                    requiredEventName ??
                                    "Sessão de Encerramento não encontrada"
                                }}
                            </span>
                            ·
                            <span class="text-neutral-200">{{
                                eligibleCount
                            }}</span>
                            elegíveis ({{ totalEntries }} SINFrões em jogo).
                            Staff e administração excluídos.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <select
                            v-if="sortedDraws.length > 1"
                            :value="selectedDrawId"
                            class="pill-container h-8 rounded-full border border-white/10 bg-black/50 px-3 text-xs text-neutral-200 focus:outline-none"
                            aria-label="Selecionar sorteio para ver participantes elegíveis"
                            @change="
                                (event) =>
                                    selectDraw(
                                        (event.target as HTMLSelectElement)
                                            .value,
                                    )
                            "
                        >
                            <option
                                v-for="draw in sortedDraws"
                                :key="draw.id"
                                :value="draw.id"
                            >
                                Elegíveis de: {{ draw.name }}
                            </option>
                        </select>

                        <div
                            v-if="eligibleParticipants.length > 0"
                            class="relative flex items-center sm:w-60"
                        >
                            <Search
                                :size="14"
                                class="pointer-events-none absolute left-3 z-10 text-neutral-400"
                            />
                            <input
                                v-model="eligibleSearch"
                                type="text"
                                placeholder="Pesquisar participante..."
                                class="pill-container h-8 w-full pr-7 pl-8 text-xs text-white placeholder-neutral-400 focus:outline-none"
                            />
                            <button
                                v-if="eligibleSearch"
                                type="button"
                                class="absolute right-2.5 z-10 cursor-pointer text-neutral-400 hover:text-white"
                                aria-label="Limpar pesquisa"
                                @click="eligibleSearch = ''"
                            >
                                <X :size="12" />
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!hasClosingEvent"
                    class="flex items-start gap-2 rounded-xl border border-red-400/20 bg-red-400/5 p-3 text-xs text-red-300"
                >
                    <AlertCircle :size="15" class="mt-0.5 shrink-0" />
                    <span>
                        Sem Sessão de Encerramento não há presenças para validar
                        e o sorteio fica bloqueado.
                    </span>
                </div>

                <div
                    class="max-h-80 w-full overflow-y-auto rounded-xl border border-white/5 bg-white/[0.01]"
                >
                    <table class="w-full border-collapse text-left">
                        <thead class="sticky top-0 z-10 bg-[#090a0c]">
                            <tr
                                class="border-b border-white/10 bg-white/[0.03] text-xs font-medium text-neutral-400"
                            >
                                <th class="w-20 px-4 py-2.5 font-medium">ID</th>
                                <th class="px-4 py-2.5 font-medium">
                                    Participante
                                </th>
                                <th class="w-44 px-4 py-2.5 font-medium">
                                    SINFrões (entradas)
                                </th>
                                <th
                                    class="w-32 px-4 py-2.5 text-right font-medium"
                                >
                                    Probabilidade
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-white/5 text-sm text-neutral-200"
                        >
                            <tr
                                v-for="participant in filteredEligible"
                                :key="participant.id"
                                class="transition-colors hover:bg-white/[0.04]"
                            >
                                <td
                                    class="px-4 py-2.5 font-mono text-xs text-neutral-500"
                                >
                                    #{{ participant.id }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-medium text-white">
                                            {{ participant.name }}
                                        </span>
                                        <span
                                            v-if="participant.email"
                                            class="text-xs text-neutral-400"
                                        >
                                            {{ participant.email }}
                                        </span>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-2.5 font-mono text-xs text-neutral-300"
                                >
                                    {{ participant.points }}
                                </td>
                                <td
                                    class="px-4 py-2.5 text-right font-mono text-xs text-neutral-400"
                                >
                                    {{ formatProbability(participant.points) }}
                                </td>
                            </tr>
                            <tr v-if="filteredEligible.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center">
                                    <div
                                        class="flex flex-col items-center justify-center gap-1.5 text-neutral-400"
                                    >
                                        <Inbox
                                            :size="20"
                                            class="text-neutral-500"
                                        />
                                        <span
                                            class="text-xs font-medium text-neutral-300"
                                        >
                                            {{
                                                eligibleParticipants.length ===
                                                0
                                                    ? "Nenhum participante elegível encontrado"
                                                    : "Nenhum participante corresponde à pesquisa"
                                            }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </CRUDView>

    <!-- Confirm Draw All Modal -->
    <Modal
        :model-value="showDrawAllModal"
        title="Sortear todos os prémios"
        description="O sorteio é imediato e fica guardado. Podes repor os vencedores depois."
        max-width="md"
        @close="showDrawAllModal = false"
    >
        <div class="flex flex-col gap-4">
            <div
                class="flex flex-col gap-2 rounded-xl border border-white/10 bg-white/5 p-4 text-xs text-neutral-300"
            >
                <p>
                    Ordem do sorteio:
                    <strong class="text-white">1º → 3º lugar</strong>.
                </p>
                <ul class="flex flex-col gap-1">
                    <li
                        v-for="draw in pendingDraws"
                        :key="draw.id"
                        class="flex items-center justify-between gap-3"
                    >
                        <span class="text-neutral-400">
                            {{ formatPlace(draw.place) }} lugar
                        </span>
                        <span class="truncate font-medium text-neutral-200">
                            {{ draw.name }}
                        </span>
                    </li>
                </ul>
                <p class="text-neutral-400">
                    Só entram participantes com presença registada na Sessão de
                    Encerramento. Staff, administração e vencedores anteriores
                    são excluídos.
                </p>
            </div>

            <div
                class="mt-2 flex items-center justify-end gap-2.5 border-t border-white/10 pt-4"
            >
                <PrimaryButton
                    type="button"
                    color="pill"
                    text-size="text-xs"
                    :disabled="isDrawingAll"
                    @click="showDrawAllModal = false"
                >
                    Cancelar
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    color="primary"
                    text-size="text-xs"
                    :disabled="isDrawingAll"
                    @click="confirmDrawAll"
                >
                    {{ isDrawingAll ? "A sortear..." : "Sortear prémios" }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>

    <!-- Confirm Reset All Modal -->
    <Modal
        :model-value="showResetAllModal"
        title="Repor sorteio"
        description="Os vencedores de todos os prémios desta edição serão removidos e os prémios voltam a poder ser sorteados."
        max-width="md"
        @close="showResetAllModal = false"
    >
        <div class="flex flex-col gap-4">
            <div
                class="flex items-start gap-2 rounded-xl border border-red-400/20 bg-red-400/5 p-4 text-xs text-red-300"
            >
                <AlertCircle :size="15" class="mt-0.5 shrink-0" />
                <span>
                    Esta ação remove os vencedores atuais. A apresentação volta
                    a mostrar “simulação” até ser feito um novo sorteio.
                </span>
            </div>

            <div
                class="mt-2 flex items-center justify-end gap-2.5 border-t border-white/10 pt-4"
            >
                <PrimaryButton
                    type="button"
                    color="pill"
                    text-size="text-xs"
                    :disabled="isResettingAll"
                    @click="showResetAllModal = false"
                >
                    Cancelar
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    color="danger"
                    text-size="text-xs"
                    :disabled="isResettingAll"
                    @click="confirmResetAll"
                >
                    {{ isResettingAll ? "A repor..." : "Repor sorteio" }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
