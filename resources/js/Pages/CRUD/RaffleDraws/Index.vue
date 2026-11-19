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
import { Dices, Inbox, RotateCcw, Search, X } from "@lucide/vue";

interface Props {
    items: Paginated<RaffleDraw>;
    with: {
        editions: Edition[];
    };
    isSearchable?: boolean;
    eligibleCount?: number;
    totalTickets?: number;
    eligibleParticipants?: EligibleParticipant[];
    selectedDrawId?: number | null;
    selectedDrawName?: string | null;
    requiredEventName?: string | null;
    activeEditionId?: number;
    isPointsBased?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isSearchable: false,
    eligibleCount: 0,
    totalTickets: 0,
    eligibleParticipants: () => [],
    selectedDrawId: null,
    selectedDrawName: null,
    requiredEventName: null,
    activeEditionId: 1,
    isPointsBased: true,
});

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);

const selectDraw = (drawId: string) => {
    const targetDraw = props.items.data.find(
        (d) => d.id.toString() === drawId.toString(),
    );
    const editionId = targetDraw
        ? targetDraw.edition_id
        : props.activeEditionId;

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
                "totalTickets",
                "selectedDrawId",
                "selectedDrawName",
                "requiredEventName",
                "activeEditionId",
                "isPointsBased",
            ],
        },
    );
};

// Search & filter eligible participants
const eligibleSearch = ref("");
const filteredEligible = computed(() => {
    const q = eligibleSearch.value.trim().toLowerCase();
    if (!q) return props.eligibleParticipants;
    return props.eligibleParticipants.filter(
        (p) =>
            p.name.toLowerCase().includes(q) ||
            (p.email && p.email.toLowerCase().includes(q)),
    );
});

const formatProbability = (tickets: number) => {
    if (!props.totalTickets || props.totalTickets <= 0) return "0.0%";
    const pct = (tickets / props.totalTickets) * 100;
    return pct < 0.1 ? "<0.1%" : `${pct.toFixed(1)}%`;
};

// Pick Winner
const raffleToDraw = ref<RaffleDraw | null>(null);
const isPickingWinner = ref(false);

const openDrawModal = (item: RaffleDraw) => {
    raffleToDraw.value = item;
    if (props.selectedDrawId !== item.id) {
        selectDraw(item.id.toString());
    }
};

const confirmPickWinner = () => {
    if (!raffleToDraw.value) return;
    isPickingWinner.value = true;
    router.post(
        route("admin.raffleDraws.pick-winner", { draw: raffleToDraw.value.id }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isPickingWinner.value = false;
                raffleToDraw.value = null;
            },
        },
    );
};

// Reset Winner
const raffleToReset = ref<RaffleDraw | null>(null);
const isResetting = ref(false);

const confirmResetWinner = () => {
    if (!raffleToReset.value) return;
    isResetting.value = true;
    router.post(
        route("admin.raffleDraws.reset-winner", {
            draw: raffleToReset.value.id,
        }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isResetting.value = false;
                raffleToReset.value = null;
            },
        },
    );
};

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

        <!-- Main CRUD Table for Raffle Prizes (Top Table) -->
        <template #header>
            <HeaderRow>
                <Header
                    filter-by="edition_id"
                    :filter-values="editions"
                    :reload-props="[
                        'items',
                        'eligibleParticipants',
                        'eligibleCount',
                        'totalTickets',
                        'selectedDrawId',
                        'selectedDrawName',
                        'requiredEventName',
                        'activeEditionId',
                        'isPointsBased',
                    ]"
                >
                    Edição
                </Header>
                <Header sort-by="name">Prémio</Header>
                <Header>Presença Obrigatória</Header>
                <Header sort-by="is_active">Estado</Header>
                <Header>Vencedor</Header>
                <Header sort-by="draw_at">Data do Sorteio</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="raffleDraws" :item="item">
                <!-- Edition -->
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>

                <!-- Prize name & description -->
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

                <!-- Evento Obrigatório -->
                <Cell class="text-xs">
                    <span
                        v-if="item.required_event?.name"
                        class="inline-flex items-center gap-1.5 text-neutral-300"
                    >
                        <span
                            class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"
                        ></span>
                        <span class="line-clamp-1">{{
                            item.required_event.name
                        }}</span>
                    </span>
                    <span v-else class="text-neutral-500">
                        Nenhum (Aberto)
                    </span>
                </Cell>

                <!-- Status -->
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
                                  ? "Por Sortear"
                                  : "Inativo"
                        }}
                    </span>
                </Cell>

                <!-- Winner -->
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

                <!-- Draw date -->
                <Cell class="text-xs whitespace-nowrap text-neutral-400">
                    {{ formatDate(item.draw_at) }}
                </Cell>

                <!-- Extra action: Sortear or Reset -->
                <template #extra-actions>
                    <button
                        v-if="item.id !== selectedDrawId"
                        type="button"
                        class="inline-flex cursor-pointer items-center gap-1 text-xs text-neutral-400 transition-colors hover:text-white"
                        title="Ver participantes elegíveis na tômbola abaixo"
                        @click="selectDraw(item.id.toString())"
                    >
                        <span>Ver elegíveis</span>
                    </button>
                    <span
                        v-else
                        class="inline-flex items-center text-xs font-medium text-amber-400"
                        title="A exibir participantes elegíveis deste sorteio abaixo"
                    >
                        <span>Na tômbola</span>
                    </span>

                    <span class="text-neutral-700">·</span>

                    <button
                        v-if="!item.winner_enrollment_id && item.is_active"
                        type="button"
                        class="inline-flex cursor-pointer items-center gap-1 font-medium text-amber-400 transition-colors hover:text-amber-300"
                        title="Sortear Vencedor"
                        @click="openDrawModal(item)"
                    >
                        <Dices :size="13" />
                        <span>Sortear</span>
                    </button>

                    <button
                        v-if="item.winner_enrollment_id"
                        type="button"
                        class="inline-flex cursor-pointer items-center gap-1 text-xs font-medium text-neutral-400 transition-colors hover:text-amber-400"
                        title="Anular vencedor e permitir novo sorteio"
                        @click="raffleToReset = item"
                    >
                        <RotateCcw :size="12" />
                        <span>Anular</span>
                    </button>

                    <span class="text-neutral-700">·</span>
                </template>
            </Row>
        </template>

        <!-- Eligible Participants Table (Under the Raffle Table) -->
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
                                Participantes Elegíveis na Tômbola
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
                                    requiredEventName
                                        ? `${requiredEventName} (apenas quem fez scan do QR code pode ganhar)`
                                        : "Nenhuma (aberto a todos os inscritos com pontos)"
                                }}
                            </span>
                            ·
                            <span class="text-neutral-200">{{
                                eligibleCount
                            }}</span>
                            elegíveis ({{ totalTickets }}
                            {{
                                isPointsBased
                                    ? "SINFrões em jogo"
                                    : "bilhetes em jogo"
                            }}). Quem já venceu um prémio é excluído dos
                            restantes.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <select
                            v-if="items.data.length > 1"
                            :value="selectedDrawId"
                            class="pill-container h-8 rounded-full border border-white/10 bg-black/50 px-3 text-xs text-neutral-200 focus:outline-none"
                            aria-label="Selecionar sorteio para ver participantes elegíveis"
                            @change="
                                (e) =>
                                    selectDraw(
                                        (e.target as HTMLSelectElement).value,
                                    )
                            "
                        >
                            <option
                                v-for="draw in items.data"
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
                                <th class="w-36 px-4 py-2.5 font-medium">
                                    {{
                                        isPointsBased
                                            ? "SINFrões (Entradas)"
                                            : "Bilhetes Comprados"
                                    }}
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
                                    {{ participant.tickets }}
                                </td>
                                <td
                                    class="px-4 py-2.5 text-right font-mono text-xs text-neutral-400"
                                >
                                    {{ formatProbability(participant.tickets) }}
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

    <!-- Confirm Sorteio Modal -->
    <Modal
        :model-value="raffleToDraw !== null"
        title="Sortear vencedor"
        description="Será selecionado aleatoriamente um participante elegível, proporcionalmente aos seus SINFrões."
        max-width="md"
        @close="raffleToDraw = null"
    >
        <div class="flex flex-col gap-4">
            <div
                v-if="raffleToDraw"
                class="flex flex-col gap-1.5 rounded-xl border border-white/10 bg-white/5 p-4 text-xs text-neutral-300"
            >
                <div>
                    Confirmar sorteio para
                    <strong class="text-white">"{{ raffleToDraw.name }}"</strong
                    >?
                </div>
                <div class="text-neutral-400">
                    Presença obrigatória:
                    <span class="font-medium text-neutral-200">
                        {{
                            raffleToDraw.required_event?.name ??
                            "Nenhuma (Aberto a todos os inscritos)"
                        }}
                    </span>
                </div>
            </div>

            <div
                class="mt-2 flex items-center justify-end gap-2.5 border-t border-white/10 pt-4"
            >
                <PrimaryButton
                    type="button"
                    color="pill"
                    text-size="text-xs"
                    :disabled="isPickingWinner"
                    @click="raffleToDraw = null"
                >
                    Cancelar
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    color="pill"
                    text-size="text-xs"
                    :disabled="isPickingWinner"
                    @click="confirmPickWinner"
                >
                    {{ isPickingWinner ? "A sortear..." : "Sortear" }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>

    <!-- Confirm Reset Winner Modal -->
    <Modal
        :model-value="raffleToReset !== null"
        title="Anular vencedor"
        description="O vencedor atual será retirado do prémio e o prémio voltará a ficar disponível para sorteio."
        max-width="md"
        @close="raffleToReset = null"
    >
        <div class="flex flex-col gap-4">
            <div
                v-if="raffleToReset"
                class="rounded-xl border border-white/10 bg-white/5 p-4 text-xs text-neutral-300"
            >
                Anular o vencedor de
                <strong class="text-white">"{{ raffleToReset.name }}"</strong>?
                <span class="mt-1 block text-neutral-400">
                    Vencedor atual:
                    {{
                        raffleToReset.winner?.participant?.user?.name ??
                        `#${raffleToReset.winner_enrollment_id}`
                    }}
                </span>
            </div>

            <div
                class="mt-2 flex items-center justify-end gap-2.5 border-t border-white/10 pt-4"
            >
                <PrimaryButton
                    type="button"
                    color="pill"
                    text-size="text-xs"
                    :disabled="isResetting"
                    @click="raffleToReset = null"
                >
                    Cancelar
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    color="danger"
                    text-size="text-xs"
                    :disabled="isResetting"
                    @click="confirmResetWinner"
                >
                    {{ isResetting ? "A anular..." : "Anular Vencedor" }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
