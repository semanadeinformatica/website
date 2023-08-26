<script setup lang="ts">
import { onMounted, ref } from "vue";

interface Props {
    modelValue: string;
    label?: string;
    id?: string;
    placeholder?: string;
    errorMessage?: string;
}

interface Emits {
    (event: "update:modelValue", value: string): void;
}

defineProps<Props>();
defineEmits<Emits>();

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute("autofocus")) input.value.focus();
});

defineExpose({ focus: () => input.value?.focus() });

defineOptions({ inheritAttrs: false });
</script>

<template>
    <div class="flex flex-col items-stretch self-stretch">
        <label
            v-if="label"
            :for="id"
            class="sr-only"
            :aria-describedby="`${id}-error`"
            >{{ label }}</label
        >
        <input
            :id="id"
            ref="input"
            :placeholder="placeholder ?? label"
            class="before:-z-1 relative border border-black bg-2023-bg shadow-2023-red shadow-md placeholder:font-semibold placeholder:text-2023-teal"
            :value="modelValue"
            v-bind="$attrs"
            @input="
                $emit(
                    'update:modelValue',
                    ($event.currentTarget as HTMLInputElement).value,
                )
            "
        />
        <span
            v-show="errorMessage"
            :id="`${id}-error`"
            class="mt-2 font-semibold text-2023-red"
            >{{ errorMessage }}</span
        >
    </div>
</template>
