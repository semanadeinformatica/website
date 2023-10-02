<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ShopItem from "@/Components/Shop/ShopItem.vue";
import type Product from "@/Types/Product";

interface Props {
    products: Product[];
}

defineProps<Props>();
</script>

<template>
    <AppLayout title="Loja">
        <template v-if="products.length > 0">
            <div
                class="flex content-center items-center justify-center self-center py-10"
            >
                <h1
                    class="w-fit border border-black bg-2023-red p-2 px-5 text-2xl font-bold text-white shadow-md shadow-2023-bg"
                >
                    Loja
                </h1>
            </div>
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
                >
                </ShopItem>
            </section>
        </template>
        <template v-else>
            <div class="flex items-center justify-center">
                <p
                    class="pt-80 text-center text-5xl font-bold text-2023-teal-dark"
                >
                    Em breve...
                </p>
            </div>
        </template>
    </AppLayout>
</template>
