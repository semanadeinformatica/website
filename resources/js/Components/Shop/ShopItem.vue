<script setup lang="ts">
import { defineProps } from 'vue';

interface Props {
    name: string;
    price: number;
    description: string;
    image: string;
}

const item = defineProps<Props>();
</script>

<template>
    <div class="flex flex-col border-2 border-black">
        <img class="border-b-2 border-black" :src="item.image">
        <div class="flex flex-col bg-2023-orange text-white px-4 py-2">
            <h2 class="text-xl font-bold">{{ item.name }}</h2>
            <div class="self-end flex flex-row gap-2 text-xl">
                <p>{{ item.price }}</p>
                <img class="w-5" src="/images/cy-sinf-small.svg">
            </div>
        </div> 
    </div>
</template>