<script setup lang="ts">
import { onMounted, ref } from "vue";

const props = defineProps({
    modelValue: String,
    label: String,
    id: String,
    placeholder: {
        type: String,
        default: null,
    },
    errorMessage: {
        type: String,
        default: null,
    },
});

defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

defineOptions({ inheritAttrs: false });
</script>

<template>
    <div class="flex flex-col items-stretch self-stretch">
        <label
            :for="id"
            class="sr-only"
            v-if="label"
            :aria-describedby="`${id}-error`"
            >{{ label }}</label
        >
        <input
            ref="input"
            :id="id"
            class="before:-z-1 relative border border-black bg-2023-bg shadow-2023-red shadow-md placeholder:font-semibold placeholder:text-2023-teal"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder ?? label"
            v-bind="$attrs"
        />
        <span
            class="mt-2 font-semibold text-2023-red"
            :id="`${id}-error`"
            v-show="errorMessage"
            >{{ errorMessage }}</span
        >
    </div>
</template>
