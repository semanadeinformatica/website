<script setup lang="ts">
import { ref } from "vue";

interface Props {
    modelValue: File | null;
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
    <label>
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
            class="h-auto max-h-20 w-full object-contain"
        />
        <div
            v-else
            class="flex h-20 w-full flex-col items-center justify-center border-2 border-dashed border-black"
        >
            <span class="text-2023-teal">Selecionar imagem</span>
        </div>
    </label>
</template>
