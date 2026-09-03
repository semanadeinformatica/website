<script setup lang="ts">
import { computed } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import { X } from "@lucide/vue";

export interface Props {
    modelValue?: boolean;
    show?: boolean;
    title?: string;
    description?: string;
    maxWidth?: "sm" | "md" | "lg" | "xl" | "2xl" | "3xl" | string;
    closeable?: boolean;
    showCloseButton?: boolean;
    contentClass?: string;
    overlayClass?: string;
    clickToClose?: boolean;
    escToClose?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: undefined,
    show: undefined,
    title: undefined,
    description: undefined,
    maxWidth: "lg",
    closeable: true,
    showCloseButton: true,
    contentClass: "",
    overlayClass: "",
    clickToClose: true,
    escToClose: true,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: boolean): void;
    (e: "close"): void;
    (e: "opened"): void;
    (e: "closed"): void;
}>();

const isOpen = computed({
    get: () => {
        if (props.modelValue !== undefined) return props.modelValue;
        if (props.show !== undefined) return props.show;
        return false;
    },
    set: (val: boolean) => {
        emit("update:modelValue", val);
        if (!val) {
            emit("close");
        }
    },
});

const maxWidthMap: Record<string, string> = {
    sm: "max-w-sm",
    md: "max-w-md",
    lg: "max-w-lg",
    xl: "max-w-xl",
    "2xl": "max-w-2xl",
    "3xl": "max-w-3xl",
};

const resolvedMaxWidth = computed(() => {
    return maxWidthMap[props.maxWidth] ?? props.maxWidth;
});

const handleClose = () => {
    isOpen.value = false;
};
</script>

<template>
    <VueFinalModal
        v-model="isOpen"
        class="z-50 flex items-center justify-center p-4"
        :overlay-class="['bg-black/80 backdrop-blur-sm', overlayClass]"
        :click-to-close="closeable && clickToClose"
        :esc-to-close="closeable && escToClose"
        :content-class="[
            'relative flex w-full max-h-[90vh] flex-col overflow-hidden rounded-3xl border border-white/10 bg-[#111215] text-white backdrop-blur-2xl',
            resolvedMaxWidth,
            contentClass,
        ]"
        @opened="emit('opened')"
        @closed="emit('closed')"
    >
        <button
            v-if="closeable && showCloseButton"
            type="button"
            class="pill-container absolute top-4 right-4 z-20 h-8 w-8 cursor-pointer justify-center text-neutral-400 transition-colors hover:text-white"
            aria-label="Fechar"
            @click="handleClose"
        >
            <X :size="16" />
        </button>

        <slot name="header">
            <div
                v-if="title || $slots.title || description"
                class="flex flex-col gap-1 p-6 pb-0 sm:p-7 sm:pb-0"
                :class="{ 'pr-12': closeable && showCloseButton }"
            >
                <div
                    v-if="$slots.title"
                    class="text-lg font-bold tracking-tight text-white sm:text-xl"
                >
                    <slot name="title" />
                </div>
                <h3
                    v-else-if="title"
                    class="text-lg font-bold tracking-tight text-white sm:text-xl"
                >
                    {{ title }}
                </h3>

                <p
                    v-if="description"
                    class="text-xs text-neutral-400 sm:text-sm"
                >
                    {{ description }}
                </p>
            </div>
        </slot>

        <div class="flex-1 overflow-y-auto p-6 sm:p-7">
            <slot name="content">
                <slot />
            </slot>
        </div>

        <div
            v-if="$slots.footer"
            class="flex flex-row items-center justify-end gap-3 border-t border-white/8 bg-white/5 px-6 py-4"
        >
            <slot name="footer" />
        </div>
    </VueFinalModal>
</template>
