<script setup lang="ts">
import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ShopItem from "@/Components/Shop/ShopItem.vue";
import FinalPrizes from "@/Components/Shop/FinalPrizes.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import ComingSoon from "@/Components/UI/ComingSoon.vue";
import QuickScroll from "@/Components/UI/QuickScroll.vue";
import { type BuyableProduct } from "@/Types/ShopPage";
import type FinalPrize from "@/Types/FinalPrize";
import { ShoppingBag, AlertTriangle } from "@lucide/vue";

interface Props {
    products: BuyableProduct[];
    finalPrizes?: FinalPrize[];
    points?: number;
    isEnrolled: boolean;
    isParticipant: boolean;
    shopEnabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    finalPrizes: () => [],
    points: undefined,
    shopEnabled: true,
});

const selectedFilter = ref<"all" | "available" | "bought" | "out-of-stock">(
    "all",
);
const sortOption = ref<"price-desc" | "price-asc" | "name">("price-desc");

const totalProducts = computed(() => props.products.length);
const availableProductsCount = computed(
    () => props.products.filter((p) => (p.stock ?? 0) > 0).length,
);
const outOfStockCount = computed(
    () => props.products.filter((p) => (p.stock ?? 0) <= 0).length,
);
const alreadyBoughtCount = computed(
    () => props.products.filter((p) => Boolean(p.alreadyBought)).length,
);

const filterOptions = computed<PillOption[]>(() => {
    const options: PillOption[] = [
        {
            id: "all",
            label: "Todos",
            count: totalProducts.value,
        },
        {
            id: "available",
            label: "Disponíveis",
            count: availableProductsCount.value,
        },
    ];

    if (alreadyBoughtCount.value > 0) {
        options.push({
            id: "bought",
            label: "Adquiridos",
            count: alreadyBoughtCount.value,
        });
    }

    if (outOfStockCount.value > 0) {
        options.push({
            id: "out-of-stock",
            label: "Esgotados",
            count: outOfStockCount.value,
        });
    }

    return options;
});

const sortLabels: Record<"price-desc" | "price-asc" | "name", string> = {
    "price-desc": "Preço: Maior primeiro",
    "price-asc": "Preço: Menor primeiro",
    name: "Nome (A-Z)",
};

const sortPillItems = computed<PillOption[]>(() => [
    {
        id: "sort",
        label: sortLabels[sortOption.value],
        dropdown: {
            align: "right",
            width: "56",
            items: [
                {
                    id: "price-desc",
                    label: "Preço: Maior primeiro",
                    active: sortOption.value === "price-desc",
                    onClick: () => {
                        sortOption.value = "price-desc";
                    },
                },
                {
                    id: "price-asc",
                    label: "Preço: Menor primeiro",
                    active: sortOption.value === "price-asc",
                    onClick: () => {
                        sortOption.value = "price-asc";
                    },
                },
                {
                    id: "name",
                    label: "Nome (A-Z)",
                    active: sortOption.value === "name",
                    onClick: () => {
                        sortOption.value = "name";
                    },
                },
            ],
        },
    },
]);

const filteredAndSortedProducts = computed(() => {
    let result = [...props.products];

    // Status filter
    if (selectedFilter.value === "available") {
        result = result.filter((p) => (p.stock ?? 0) > 0);
    } else if (selectedFilter.value === "out-of-stock") {
        result = result.filter((p) => (p.stock ?? 0) <= 0);
    } else if (selectedFilter.value === "bought") {
        result = result.filter((p) => Boolean(p.alreadyBought));
    }

    // Sorting
    if (sortOption.value === "price-desc") {
        result.sort((a, b) => b.price - a.price);
    } else if (sortOption.value === "price-asc") {
        result.sort((a, b) => a.price - b.price);
    } else if (sortOption.value === "name") {
        result.sort((a, b) => a.name.localeCompare(b.name));
    }

    return result;
});
</script>

<template>
    <AppLayout title="Prémios & Loja">
        <QuickScroll mode="top" />

        <div
            class="relative mx-auto w-full max-w-7xl space-y-8 px-4 py-8 sm:space-y-10 sm:px-6 sm:py-12 lg:px-8"
        >
            <div
                v-if="!shopEnabled"
                class="flex items-start gap-3 rounded-2xl border border-amber-500/25 bg-amber-500/10 p-4 text-amber-200"
            >
                <AlertTriangle :size="18" class="mt-0.5 shrink-0" />
                <div class="space-y-1">
                    <p class="text-sm font-semibold">
                        Loja temporariamente desativada
                    </p>
                    <p class="text-xs leading-relaxed text-amber-200/80">
                        Os participantes não conseguem aceder à loja nem efetuar
                        compras enquanto esta opção estiver ativa. Podes
                        reativá-la na página de produtos do painel de
                        administração.
                    </p>
                </div>
            </div>

            <!-- Final Prizes Section -->
            <FinalPrizes :prizes="finalPrizes" />

            <!-- Products Section -->
            <section class="space-y-6">
                <h2
                    class="text-center text-2xl font-extrabold tracking-tight text-white sm:text-3xl"
                >
                    Loja
                </h2>

                <!-- Filter and Sort Bar (items-start on mobile prevents stretching) -->
                <div
                    v-if="products.length > 0"
                    class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <!-- Status Filter Pills -->
                    <PillSelector
                        v-model="selectedFilter"
                        :items="filterOptions"
                        size="sm"
                    />

                    <!-- Dropdown Sort with PillSelector -->
                    <PillSelector :items="sortPillItems" size="sm" />
                </div>

                <!-- Products Grid -->
                <div
                    v-if="filteredAndSortedProducts.length > 0"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                >
                    <ShopItem
                        v-for="product in filteredAndSortedProducts"
                        :key="product.id"
                        :product="product"
                        :points="points"
                        :is-participant="isParticipant"
                        :is-enrolled="isEnrolled"
                    />
                </div>

                <!-- Empty Filter Results -->
                <div
                    v-else-if="products.length > 0"
                    class="flex flex-col items-center justify-center rounded-3xl border border-white/8 bg-black/30 py-16 text-center"
                >
                    <ShoppingBag
                        :size="36"
                        class="mb-3 text-neutral-600 opacity-40"
                    />
                    <h3 class="text-base font-semibold text-white">
                        Nenhum artigo encontrado
                    </h3>
                    <p class="mt-1 max-w-sm text-xs text-neutral-400">
                        Não existem artigos correspondentes aos filtros
                        selecionados.
                    </p>
                    <div class="mt-5">
                        <PrimaryButton
                            padding="px-4 py-1.5"
                            text-size="text-xs"
                            @click="selectedFilter = 'all'"
                        >
                            Limpar filtros
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Empty Shop State (Coming soon) -->
                <ComingSoon v-else class="py-24" />
            </section>
        </div>
    </AppLayout>
</template>
