<script setup lang="ts">
import type Product from "@/Types/Product";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import type { User } from "@/Types/User";
import { router } from "@inertiajs/vue3";
import route from "ziggy-js";

const options = ref({
    modelValue: false,
});

interface Props {
    product: Product;
    user?: User;
    isEnrolled?: boolean;
    isParticipant?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div class="flex max-w-[20em] flex-col border-2 border-black">
        <img
            class="object-fit max-w-[20em] border-b-2 border-black p-4"
            :src="product.image_product_url"
        />
        <div
            class="flex flex-1 cursor-pointer flex-col justify-between bg-2023-orange px-4 py-2 text-white"
            @click="options.modelValue = true"
        >
            <h2 class="text-xl font-bold">{{ product.name }}</h2>
            <div class="flex flex-row gap-2 self-end text-xl">
                <p>{{ product.price }}</p>
                <img
                    class="w-5"
                    title="SINFrão"
                    src="/images/cy-sinf-small.svg"
                />
            </div>
        </div>
    </div>
    <VueFinalModal
        v-if="isParticipant || $page.props.auth.user === null"
        v-model="options.modelValue"
        class="flex items-center justify-center"
        content-class="max-w-xl mx-4 p-4 bg-2023-bg border border-black border-solid flex relative justify-center items-center flex-col gap-8"
    >
        <template v-if="isEnrolled">
            <img
                :src="product.image_product_url"
                class="object-fit w-48 justify-center"
            />
            <p class="text-2023-teal-dark">
                Confirmar compra de <b>{{ product.name }}</b> por
                {{ product.price }}?
            </p>
            <!-- TODO: BACKEND CONNECTION -->
            <PrimaryButton>Comprar</PrimaryButton>
        </template>
        <template v-else>
            <p class="text-2023-teal-dark">
                Antes de comprares este produto, inscreve-te!
            </p>
            <PrimaryButton
                color="orange"
                shadow="teal"
                text-size="text-xl"
                padding="sm:px-8"
                @click="
                    $page.props.auth.user
                        ? router.put(route('enroll'))
                        : router.get(route('register'))
                "
                >Inscrever-me</PrimaryButton
            >
        </template>
    </VueFinalModal>
</template>
