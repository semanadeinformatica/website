<script setup lang="ts">
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import ShopItem from "@/Components/Shop/ShopItem.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import QuickScroll from "@/Components/UI/QuickScroll.vue";
import { type BuyableProduct } from "@/Types/ShopPage";
import { type User } from "@/Types/User";
import { ShoppingBag, ArrowRight } from "@lucide/vue";

interface Props {
    products: BuyableProduct[];
    points?: number;
    isEnrolled: boolean;
    isParticipant: boolean;
}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user as User | undefined);

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
    <AppLayout title="Loja">
        <QuickScroll mode="top" />

        <div
            class="relative mx-auto w-full max-w-7xl space-y-8 px-4 py-8 sm:space-y-10 sm:px-6 sm:py-12 lg:px-8"
        >
            <!-- User Balance Pill / Enrollment CTA -->
            <div
                v-if="points !== null && points !== undefined"
                class="flex justify-center"
            >
                <PillSelector size="md" :wrap="false">
                    <div
                        class="pill-item cursor-default gap-2.5 hover:bg-transparent"
                    >
                        <span class="text-neutral-400">O teu saldo:</span>
                        <span
                            class="flex items-center gap-1.5 font-bold text-white"
                        >
                            <span class="font-mono text-base font-bold">{{
                                points
                            }}</span>
                            <img
                                class="h-4 w-4 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </span>
                    </div>
                </PillSelector>
            </div>

            <div v-else-if="user && !isEnrolled" class="flex justify-center">
                <PrimaryButton
                    padding="px-4 py-1.5"
                    text-size="text-xs"
                    @click="router.put(route('enroll'))"
                >
                    <span>Inscreve-te na SINF 2026 para ganhares SINFrões</span>
                    <ArrowRight :size="13" class="text-amber-400" />
                </PrimaryButton>
            </div>

            <!-- Products Section -->
            <section class="space-y-6">
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
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-24 text-center"
                >
                    <PillSelector
                        :items="[
                            {
                                id: 'soon',
                                label: 'Em breve...',
                                disabled: true,
                            },
                        ]"
                        size="sm"
                        :wrap="false"
                        container-class="mb-4"
                    />
                    <h3 class="text-lg font-bold text-white">
                        A loja oficial da SINF 2026 estará disponível em breve
                    </h3>
                    <p
                        class="mt-2 max-w-md text-xs leading-relaxed text-neutral-400 sm:text-sm"
                    >
                        Estamos a preparar artigos exclusivos para esta edição.
                        Fica atento às novidades no programa e redes sociais!
                    </p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
