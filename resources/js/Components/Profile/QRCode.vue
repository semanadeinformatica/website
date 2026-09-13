<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { router } from "@inertiajs/vue3";
import { QrCode } from "@lucide/vue";
import { computed, ref } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
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
    <PrimaryButton
        v-bind="$attrs"
        type="button"
        color="pill"
        padding="px-3.5 py-2 sm:px-4 sm:py-2"
        aria-label="Ver QR Code"
        @click="showQRCode"
    >
        <QrCode :size="15" />
        <span>QR Code</span>
    </PrimaryButton>

    <Modal
        v-model="modalOpen"
        max-width="md"
        title="Código do Participante"
        description="Apresenta este código QR ou código numérico nas bancas e eventos."
    >
        <div class="flex flex-col items-center justify-center gap-6 py-2">
            <span v-if="loading" class="text-sm text-neutral-400"
                >A gerar código...</span
            >
            <template v-else-if="participant.quest_qr_code || participant.quest_code">
                <div
                    v-if="participant.quest_qr_code"
                    class="flex items-center justify-center rounded-2xl bg-white p-4"
                    v-html="participant.quest_qr_code"
                />
                <div
                    v-if="participant.quest_code"
                    class="flex flex-col items-center gap-1 text-center"
                >
                    <code
                        class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 font-mono text-xl font-bold tracking-widest text-white"
                    >
                        {{ participant.quest_code }}
                    </code>
                </div>
            </template>
            <div v-else class="py-4 text-center text-sm text-neutral-400">
                Não foi possível carregar o código. Tenta novamente mais tarde.
            </div>
        </div>

        <template #footer>
            <div class="flex w-full justify-end">
                <PrimaryButton
                    color="pill"
                    padding="px-5 py-1.5"
                    text-size="text-xs"
                    @click="modalOpen = false"
                >
                    Fechar
                </PrimaryButton>
            </div>
        </template>
    </Modal>
</template>
