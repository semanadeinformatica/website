<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { router } from "@inertiajs/vue3";
import { QrCode } from "@lucide/vue";
import { computed, ref } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import { route } from "ziggy-js";

interface Props {
    participant: Participant;
}

const props = defineProps<Props>();

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
        type="button"
        class="text-text-color flex w-fit cursor-pointer items-center justify-center rounded-full hover:opacity-80"
        aria-label="Ver QR Code"
        @click="showQRCode"
    >
        <QrCode :size="22" />
    </button>

    <Modal
        v-model="modalOpen"
        max-width="md"
        title="Código do Participante"
        description="Apresenta este código QR ou código numérico nas bancas e eventos."
    >
        <div class="flex flex-col items-center justify-center gap-6 py-2">
            <span v-if="loading" class="text-sm text-neutral-400">A gerar código...</span>
            <template v-else>
                <div
                    v-if="participant.quest_qr_code"
                    class="rounded-2xl bg-white p-4 shadow-lg flex items-center justify-center"
                    v-html="participant.quest_qr_code"
                />
                <div
                    v-if="participant.quest_code"
                    class="pill-container px-4 py-1.5 font-mono text-base font-bold text-white tracking-widest"
                >
                    {{ participant.quest_code }}
                </div>
            </template>
        </div>
    </Modal>
</template>

