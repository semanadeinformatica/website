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
        <template v-if="products.length > 0">
            <div
                class="flex content-center items-center justify-center self-center py-4"
            >
              <h2
                id="aboutus"
                class=" mb-5 w-fit rounded-md bg-2025-blue p-3 px-6 text-3xl font-bold text-white xl:text-3xl 2xl:text-4xl"
            >
                Loja
            </h2>
                
            </div>
            <p
                v-if="points !== null"
                class="py-10 text-center text-xl font-bold text-text-color"
            >
                Tens {{ points }}
                <img
                    class="inline w-5 align-text-top"
                    alt="SINFrão"
                    title="SINFrão"
                    src="/images/cy-sinf-small.svg"
                />
            </p>
            <section
                class="align-center grid justify-center gap-10 px-32"
                style="
                    grid-template-columns: repeat(
                        auto-fill,
                        minmax(18em, 20em)
                    );
                "
            >
                <ShopItem
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                    :is-participant="isParticipant"
                    :is-enrolled="isEnrolled"
                />
            </section>
        </template>
        <template v-else>
            <div class="flex items-center justify-center">
                <p
                    class="pt-80 text-center text-5xl font-bold text-white"
                >
                    Em breve...
                </p>
            </div>
        </template>
    </AppLayout>
</template>
