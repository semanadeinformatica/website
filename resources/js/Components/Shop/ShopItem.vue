<script setup lang="ts">
import type Product from "@/Types/Product";

interface Props {
    product: Product;
}

defineProps<Props>();
</script>

<template>
    <div class="flex max-w-[20em] flex-col border-2 border-black">
        <img
            class="h-[20em] max-w-[20em] border-b-2 border-black object-cover"
            :src="product.image_product_url"
        />
        <div class="flex flex-col bg-2023-orange px-4 py-2 text-white">
            <h2 class="text-xl font-bold">{{ product.name }}</h2>
            <div class="flex flex-row gap-2 self-end text-xl">
                <p>{{ product.price }}</p>
                <img class="w-5" src="/images/cy-sinf-small.svg" />
            </div>
        </div>
    </div>
</template>
