<script setup lang="ts">
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/UI/Modal.vue";
import PaginationLinks from "@/Components/UI/PaginationLinks.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import useSearch from "@/Composables/useSearch";
import type Paginated from "@/Types/Paginated";
import {
    AlertTriangle,
    Check,
    Coins,
    Inbox,
    Package,
    Receipt,
    RotateCcw,
    Search,
    Users,
    X,
} from "@lucide/vue";

interface EditionOption {
    id: number;
    name: string;
    year: string | number;
    shop_enabled: boolean;
}

interface CurrentEdition {
    id: number;
    name: string;
    shop_enabled: boolean;
}

interface Purchase {
    enrollment_id: number;
    product_id: number;
    redeemed: boolean;
    price: number;
    product_name: string | null;
    product_stock: number | null;
    participant_name: string | null;
    participant_email: string | null;
}

interface Summary {
    purchases: number;
    participants: number;
    points: number;
    pending: number;
}

interface Filters {
    edition_id: string;
    query: string;
}

interface Props {
    purchases: Paginated<Purchase>;
    summary: Summary;
    filters: Filters;
    editions: EditionOption[];
    currentEdition?: CurrentEdition | null;
}

const props = defineProps<Props>();

const search = useSearch("query", [
    "purchases",
    "summary",
    "filters",
    "currentEdition",
]);

const selectedPurchase = ref<Purchase | null>(null);
const isRefunding = ref(false);

const currentEdition = computed(() => props.currentEdition ?? null);

const summaryCards = computed(() => [
    {
        label: "Compras",
        value: props.summary.purchases,
        icon: Receipt,
    },
    {
        label: "Participantes",
        value: props.summary.participants,
        icon: Users,
    },
    {
        label: "SINFrões gastos",
        value: props.summary.points,
        icon: Coins,
    },
    {
        label: "Por entregar",
        value: props.summary.pending,
        icon: Package,
    },
]);

const changeEdition = (event: Event) => {
    const value = (event.target as HTMLSelectElement).value;

    router.get(
        route("admin.shop.purchases"),
        {
            edition_id: value,
            query: search.value.trim() || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ["purchases", "summary", "filters", "currentEdition"],
        },
    );
};

const openRefundModal = (purchase: Purchase) => {
    selectedPurchase.value = purchase;
};

const closeRefundModal = () => {
    if (isRefunding.value) return;
    selectedPurchase.value = null;
};

const confirmRefund = () => {
    const purchase = selectedPurchase.value;

    if (purchase === null || isRefunding.value) return;

    isRefunding.value = true;

    router.post(
        route("admin.shop.purchases.refund", {
            product: purchase.product_id,
            enrollment: purchase.enrollment_id,
        }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isRefunding.value = false;
                selectedPurchase.value = null;
            },
        },
    );
};
</script>

<template>
    <AdminLayout title="Compras da Loja">
        <template #header-actions>
            <div
                class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center"
            >
                <select
                    :value="filters.edition_id"
                    class="pill-container h-8 cursor-pointer px-3 text-xs text-white focus:outline-none"
                    aria-label="Filtrar por edição"
                    @change="changeEdition"
                >
                    <option value="all">Todas as edições</option>
                    <option
                        v-for="edition in editions"
                        :key="edition.id"
                        :value="edition.id"
                    >
                        {{ edition.name }}
                    </option>
                </select>

                <div class="relative flex items-center sm:w-64">
                    <Search
                        :size="14"
                        class="pointer-events-none absolute left-3 z-10 text-neutral-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Pesquisar participante ou produto..."
                        class="pill-container h-8 w-full pr-8 pl-8.5 text-xs text-white placeholder-neutral-400 focus:outline-none"
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
            </div>
        </template>

        <div class="flex flex-col gap-6">
            <div
                v-if="currentEdition && !currentEdition.shop_enabled"
                class="flex items-start gap-3 rounded-2xl border border-amber-500/25 bg-amber-500/10 p-4 text-amber-200"
            >
                <AlertTriangle :size="18" class="mt-0.5 shrink-0" />
                <div class="space-y-1">
                    <p class="text-sm font-semibold">
                        A loja de {{ currentEdition.name }} está desativada
                    </p>
                    <p class="text-xs leading-relaxed text-amber-200/80">
                        Os participantes não conseguem ver a loja nem comprar.
                        Podes reativá-la na página de produtos.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="rounded-3xl border border-white/8 bg-black/50 p-5 backdrop-blur-md transition-colors hover:border-white/15"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-2xl font-bold text-white sm:text-3xl">
                            {{ card.value }}
                        </div>
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-neutral-400"
                        >
                            <component :is="card.icon" :size="16" />
                        </div>
                    </div>
                    <div
                        class="mt-2 font-mono text-[11px] font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        {{ card.label }}
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-white/10 bg-black/40 shadow-[0_4px_24px_rgba(0,0,0,0.3)] backdrop-blur-md"
            >
                <div class="w-full overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-white/10 bg-white/[0.03] text-xs font-medium text-neutral-400"
                            >
                                <th class="px-5 py-3 font-medium">
                                    Participante
                                </th>
                                <th class="px-5 py-3 font-medium">Artigo</th>
                                <th class="px-5 py-3 font-medium">
                                    Valor pago
                                </th>
                                <th class="px-5 py-3 font-medium">Estado</th>
                                <th class="px-5 py-3 text-right font-medium">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-white/5 text-sm text-neutral-200"
                        >
                            <tr
                                v-for="purchase in purchases.data"
                                :key="`${purchase.enrollment_id}-${purchase.product_id}`"
                                class="transition-colors hover:bg-white/[0.04]"
                            >
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/8 text-xs font-bold text-neutral-300"
                                        >
                                            {{
                                                (
                                                    purchase.participant_name ??
                                                    "P"
                                                ).charAt(0)
                                            }}
                                        </div>
                                        <div class="flex min-w-0 flex-col">
                                            <span
                                                class="truncate text-xs font-semibold text-white"
                                            >
                                                {{
                                                    purchase.participant_name ??
                                                    `Inscrição #${purchase.enrollment_id}`
                                                }}
                                            </span>
                                            <span
                                                class="truncate text-[11px] text-neutral-400"
                                            >
                                                {{
                                                    purchase.participant_email ??
                                                    "Sem email"
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-white">
                                            {{
                                                purchase.product_name ??
                                                `Produto #${purchase.product_id}`
                                            }}
                                        </span>
                                        <span
                                            v-if="
                                                purchase.product_stock !== null
                                            "
                                            class="text-[11px] text-neutral-500"
                                        >
                                            {{ purchase.product_stock }} em
                                            stock
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-3">
                                    <div
                                        class="flex items-center gap-1.5 font-mono text-xs font-semibold text-neutral-200"
                                    >
                                        <Coins
                                            :size="13"
                                            class="text-amber-400"
                                        />
                                        <span>{{ purchase.price }}</span>
                                    </div>
                                </td>

                                <td class="px-5 py-3">
                                    <span
                                        v-if="purchase.redeemed"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-1 text-[11px] font-semibold text-emerald-400 ring-1 ring-emerald-500/25"
                                    >
                                        <Check :size="12" />
                                        Entregue
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-500/15 px-2.5 py-1 text-[11px] font-semibold text-amber-400 ring-1 ring-amber-500/25"
                                    >
                                        <Package :size="12" />
                                        Por entregar
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-right">
                                    <button
                                        type="button"
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1.5 text-[11px] font-semibold text-red-300 transition-colors hover:border-red-500/35 hover:bg-red-500/20 hover:text-red-200"
                                        @click="openRefundModal(purchase)"
                                    >
                                        <RotateCcw :size="12" />
                                        <span>Reembolsar</span>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="purchases.data.length === 0">
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div
                                        class="flex flex-col items-center justify-center gap-2"
                                    >
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-neutral-400"
                                        >
                                            <Inbox :size="24" />
                                        </div>
                                        <span
                                            class="text-sm font-medium text-white"
                                        >
                                            Nenhuma compra encontrada
                                        </span>
                                        <span class="text-xs text-neutral-400">
                                            {{
                                                filters.query
                                                    ? "Tente ajustar a pesquisa."
                                                    : "Ainda ninguém comprou produtos nesta edição."
                                            }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <PaginationLinks :links="purchases.links" />
        </div>

        <Modal
            :model-value="selectedPurchase !== null"
            title="Reembolsar e remover compra"
            description="Esta ação devolve os SINFrões ao participante, repõe o stock e remove a compra."
            max-width="md"
            :close-disabled="isRefunding"
            :click-to-close="!isRefunding"
            :esc-to-close="!isRefunding"
            @close="closeRefundModal"
        >
            <div v-if="selectedPurchase" class="flex flex-col gap-4">
                <div
                    class="flex flex-col gap-2 rounded-xl border border-white/10 bg-white/5 p-4 text-xs text-neutral-300"
                >
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-neutral-400">Participante</span>
                        <span class="truncate font-medium text-white">
                            {{
                                selectedPurchase.participant_name ??
                                `Inscrição #${selectedPurchase.enrollment_id}`
                            }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-neutral-400">Artigo</span>
                        <span class="truncate font-medium text-white">
                            {{
                                selectedPurchase.product_name ??
                                `Produto #${selectedPurchase.product_id}`
                            }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-neutral-400">Reembolso</span>
                        <span class="font-mono font-bold text-emerald-400">
                            +{{ selectedPurchase.price }} SINFrões
                        </span>
                    </div>
                </div>

                <div
                    class="flex items-start gap-2 rounded-xl border border-amber-500/20 bg-amber-500/5 p-3 text-xs text-amber-200"
                >
                    <AlertTriangle :size="15" class="mt-0.5 shrink-0" />
                    <span>
                        O stock do artigo vai aumentar uma unidade e a compra
                        será removida definitivamente. Esta ação não pode ser
                        revertida.
                    </span>
                </div>
            </div>

            <template #footer>
                <PrimaryButton
                    type="button"
                    color="pill"
                    text-size="text-xs"
                    :disabled="isRefunding"
                    @click="closeRefundModal"
                >
                    Cancelar
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    color="danger"
                    text-size="text-xs"
                    :disabled="isRefunding"
                    @click="confirmRefund"
                >
                    {{
                        isRefunding ? "A reembolsar..." : "Reembolsar e remover"
                    }}
                </PrimaryButton>
            </template>
        </Modal>
    </AdminLayout>
</template>
