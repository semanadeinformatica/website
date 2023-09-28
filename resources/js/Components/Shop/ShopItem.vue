<script setup lang="ts">
import type Product from "@/Types/Product";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";

const options = ref({
    modelValue: false,
});

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
        <div
            class="flex cursor-pointer flex-col bg-2023-orange px-4 py-2 text-white"
            @click="options.modelValue = true"
        >
            <h2 class="text-xl font-bold">{{ product.name }}</h2>
            <div class="flex flex-row gap-2 self-end text-xl">
                <p>{{ product.price }}</p>
                <img class="w-5" src="/images/cy-sinf-small.svg" />
            </div>
        </div>
    </div>
    <VueFinalModal
        v-model="options.modelValue"
        class="flex items-center justify-center"
        content-class="max-w-xl mx-4 p-4 bg-2023-bg border border-black border-solid flex relative justify-center items-center flex-col gap-8"
    >
        <img
            :src="product.image_product_url"
            class="w-auto justify-center border border-solid border-black shadow-xl shadow-2023-teal-dark"
        />
        <p>
            Confirmar compra de <b>{{ product.name }}</b> por
            {{ product.price }}?
        </p>
        <PrimaryButton> Comprar </PrimaryButton>
    </VueFinalModal>
</template>
