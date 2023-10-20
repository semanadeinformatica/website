<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { router } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import { ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import route from "ziggy-js";

defineProps<{
    participant: Participant;
}>();

const options = ref({
    modelValue: false,
});

const showQRCode = () => {
    options.value.modelValue = true;

    router.post(route("generate-quest-code"));
};
</script>

<template>
    <p
        class="flex w-fit cursor-pointer rounded-full text-2023-teal"
        @click="showQRCode"
    >
        <OhVueIcon name="io-qr-code" scale="1.4"></OhVueIcon>
    </p>
    <VueFinalModal
        v-model="options.modelValue"
        class="flex items-center justify-center"
        content-class="max-w-xl min-w-[20em] mx-4 p-6 gap-7 bg-2023-bg border border-black border-solid flex relative justify-center items-center flex-col"
    >
        <div v-html="participant.quest_qr_code"></div>
        <p class="font-bold text-2023-teal-dark">
            {{ participant.quest_code }}
        </p>
    </VueFinalModal>
</template>

<style>
.vfm__overlay {
    background-color: rgba(248, 245, 231, 0.5);
}
</style>
