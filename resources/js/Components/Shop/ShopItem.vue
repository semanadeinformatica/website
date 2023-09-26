<script setup lang="ts">
import type Product from '@/Types/Product';
import { defineProps } from 'vue';

interface Props {
    product: Product;
}

const item = defineProps<Props>();
</script>

<template>
    <div class="flex flex-col border-2 border-black max-w-[20em]">
        <img class="border-b-2 border-black max-w-[20em] h-[20em] object-cover" src="https://picsum.photos/200/300">
        <div class="flex flex-col bg-2023-orange text-white px-4 py-2">
            <h2 class="text-xl font-bold">{{ product.name }}</h2>
            <div class="self-end flex flex-row gap-2 text-xl">
                <p>{{ product.price }}</p>
                <img class="w-5" src="/images/cy-sinf-small.svg">
            </div>
        </div> 
    </div>
</template>