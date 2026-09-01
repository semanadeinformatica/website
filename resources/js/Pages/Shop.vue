<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ShopItem from "@/Components/Shop/ShopItem.vue";
import { type BuyableProduct } from "@/Types/ShopPage";

interface Props {
    products: BuyableProduct[];
    points?: number;
    isEnrolled: boolean;
    isParticipant: boolean;
}

defineProps<Props>();
</script>

<template>
    <AppLayout title="Loja">
        <div class="relative mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div
                v-if="points !== null && points !== undefined"
                class="mb-10 flex justify-center"
            >
                <div
                    class="pill-container gap-2.5 px-5 py-2 text-xs text-neutral-300 shadow-none sm:text-sm"
                >
                    <span>O teu saldo:</span>
                    <span
                        class="flex items-center gap-1.5 font-bold text-white"
                    >
                        <span class="text-sm font-semibold sm:text-base">{{
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
            </div>

            <template v-if="products.length > 0">
                <section
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 md:grid-cols-3 lg:grid-cols-4"
                >
                    <ShopItem
                        v-for="product in products"
                        :key="product.id"
                        :product="product"
                        :points="points"
                        :is-participant="isParticipant"
                        :is-enrolled="isEnrolled"
                    />
                </section>
            </template>

            <div
                v-else
                class="flex flex-col items-center justify-center py-28 text-center"
            >
                <div class="pill-container mb-4 px-6 py-2.5">
                    <span class="text-sm font-medium text-neutral-400">
                        Em breve...
                    </span>
                </div>
                <p class="max-w-md text-sm text-neutral-400">
                    A loja da edição 2026 estará disponível brevemente. Fica
                    atento às novidades!
                </p>
            </div>
        </div>
    </AppLayout>
</template>
