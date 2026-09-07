<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ShopItem from "@/Components/Shop/ShopItem.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
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
        <div class="relative mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <div
                v-if="points !== null && points !== undefined"
                class="mb-10 flex justify-center"
            >
                <PillSelector size="md" :wrap="false">
                    <div
                        class="pill-item cursor-default gap-2.5 hover:bg-transparent"
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
                </PillSelector>
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
                <PillSelector
                    :items="[
                        { id: 'soon', label: 'Em breve...', disabled: true },
                    ]"
                    size="sm"
                    :wrap="false"
                    container-class="mb-4"
                />
                <p class="max-w-md text-sm text-neutral-400">
                    A loja da edição 2026 estará disponível brevemente. Fica
                    atento às novidades!
                </p>
            </div>
        </div>
    </AppLayout>
</template>
