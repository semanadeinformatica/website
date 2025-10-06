<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { router } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import { computed, ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import route from "ziggy-js";

const props = defineProps<{
    participant: Participant;
}>();

const participant = computed(() => props.participant);

const modalOpen = ref(false);
const loading = ref(false);

const showQRCode = () => {
    modalOpen.value = true;

    if (!participant.value.quest_qr_code) {
        loading.value = true;
        router.post(route("generate-quest-code"), undefined, {
            preserveState: true,
            onFinish: () => (loading.value = false),
        });
    }
};
</script>

<template>
    <button
        class="flex w-fit cursor-pointer rounded-full text-text-color"
        @click="showQRCode"
    >
        <OhVueIcon name="io-qr-code" scale="1.2"></OhVueIcon>
    </button>
    <VueFinalModal
        v-model="modalOpen"
        class="flex items-center justify-center"
        content-class="max-w-xl min-w-[20em] mx-4 p-8 pt-12 gap-8 bg-white border border-black border-solid flex relative justify-center items-center flex-col"
    >
        <span v-if="loading" class="text-lg">A gerar...</span>
        <template v-else>
            <div v-html="participant.quest_qr_code"></div>
            <span class="font-mono font-bold text-2023-teal-dark">
                {{ participant.quest_code }}
            </span>
        </template>
    </VueFinalModal>
</template>

<style>
.vfm__overlay {
    background-color: rgba(248, 245, 231, 0.5);
}
</style>
