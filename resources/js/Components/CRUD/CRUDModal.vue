<script setup lang="ts">
import { computed } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    title: string;
    description?: string;
    name?: string;
    backUrl?: string;
    maxWidth?: "sm" | "md" | "lg" | "xl" | "2xl" | "3xl" | string;
    submitLabel?: string;
    cancelLabel?: string;
    processing?: boolean;
    hideFooter?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    description: "",
    name: "",
    backUrl: "",
    maxWidth: "lg",
    submitLabel: "",
    cancelLabel: "Cancelar",
    processing: false,
    hideFooter: false,
});

const emit = defineEmits<{
    (e: "close"): void;
    (e: "submit"): void;
}>();

const close = () => {
    emit("close");
    if (props.backUrl) {
        router.visit(props.backUrl, { preserveScroll: true });
    } else if (props.name) {
        router.visit(route(`admin.${props.name}.index`), {
            preserveScroll: true,
        });
    } else {
        window.history.back();
    }
};

const computedSubmitLabel = computed(() => {
    if (props.submitLabel) return props.submitLabel;
    const lower = props.title.toLowerCase();
    if (
        lower.startsWith("editar") ||
        lower.startsWith("atualizar") ||
        lower.startsWith("alterar")
    ) {
        return "Guardar alterações";
    }
    return "Criar";
});
</script>

<template>
    <Modal
        :model-value="true"
        :title="title"
        :description="description"
        :max-width="maxWidth"
        @close="close"
    >
        <form
            id="crud-modal-form"
            class="flex flex-col gap-4.5"
            @submit.prevent="emit('submit')"
        >
            <slot :close="close" />

            <div
                v-if="!hideFooter"
                class="mt-2 flex items-center justify-end gap-2.5 border-t border-white/10 pt-4"
            >
                <PrimaryButton
                    type="button"
                    color="pill"
                    text-size="text-xs"
                    @click="close"
                >
                    {{ cancelLabel }}
                </PrimaryButton>

                <PrimaryButton
                    type="submit"
                    color="pill"
                    text-size="text-xs"
                    :disabled="processing"
                >
                    {{ computedSubmitLabel }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
