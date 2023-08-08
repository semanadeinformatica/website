<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: String,
    label: String,
    placeholder: {
        type: String,
        default: null,
    },
});

defineEmits(['update:modelValue']);

const input = ref(null);

const placeholder = props.placeholder ?? props.label;

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

defineOptions({ inheritAttrs: false });
</script>

<template>
    <label class="self-stretch flex flex-col items-stretch">
        <span class="sr-only" v-if="label">{{ label }}</span>
        <input
            ref="input"
            class="border border-black bg-2023-bg before:-z-1 relative shadow-md shadow-2023-red"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder"
            v-bind="$attrs"
        >
    </label>
</template>
