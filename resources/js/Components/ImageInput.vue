<script setup lang="ts">
import { Image as ImageIcon } from "@lucide/vue";
import { ref } from "vue";

interface Props {
    modelValue: File | null;
    label?: string;
    id?: string;
    initialPreview?: string;
    errorMessage?: string;
}

interface Emits {
    (event: "update:modelValue", value: File | null): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const input = ref<HTMLInputElement | null>(null);
const preview = ref(props.initialPreview);

const updatePreview = () => {
    const photo = input.value?.files?.[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        preview.value = (e.target?.result as string) ?? null;
    };

    reader.readAsDataURL(photo);

    emit("update:modelValue", photo);
};
</script>

<template>
    <label class="flex cursor-pointer flex-col items-center">
        <input
            ref="input"
            type="file"
            class="sr-only"
            @change="updatePreview"
        />

        <img
            v-if="preview"
            :src="preview"
            alt=""
            class="shadow-2023-red max-h-40 w-auto border border-black object-contain shadow-md"
        />

        <ImageIcon v-else :size="80" class="text-neutral-400" />

        <span class="sr-only">{{ label }}</span>

        <span
            v-show="errorMessage"
            :id="`${id}-error`"
            class="text-2023-red mt-2 font-semibold"
            >{{ errorMessage }}</span
        >
    </label>
</template>
