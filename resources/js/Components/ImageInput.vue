<script setup lang="ts">
import { OhVueIcon } from "oh-vue-icons";
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
            class="max-h-40 w-auto border border-black object-contain shadow-md shadow-2023-red"
        />

        <OhVueIcon v-else name="io-image" class="h-20 w-20" />

        <span class="sr-only">{{ label }}</span>

        <span
            v-show="errorMessage"
            :id="`${id}-error`"
            class="mt-2 font-semibold text-2023-red"
            >{{ errorMessage }}</span
        >
    </label>
</template>
