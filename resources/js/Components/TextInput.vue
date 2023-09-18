<script setup lang="ts">
import { onMounted, ref, type InputHTMLAttributes } from "vue";

interface Props {
    modelValue: string;
    label?: string;
    id?: string;
    placeholder?: string;
    errorMessage?: string;
    type?: InputHTMLAttributes["type"] | "select" | "textarea";
}

interface Emits {
    (event: "update:modelValue", value: string): void;
}

defineProps<Props>();
defineEmits<Emits>();

const input = ref<
    HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement | null
>(null);

onMounted(() => {
    if (input.value?.hasAttribute("autofocus")) input.value.focus();
});

defineExpose({ focus: () => input.value?.focus() });

defineOptions({ inheritAttrs: false });

const baseClass =
    "peer border border-black bg-2023-bg shadow-md shadow-2023-red px-3 pb-2 pt-6 disabled:opacity-50 disabled:text-gray-500 disabled:shadow-gray-500 disabled:shadow-md";

const visible = ref(false);
</script>

<template>
    <div class="relative flex flex-col items-stretch self-stretch">
        <select
            v-if="type === 'select'"
            :id="id"
            ref="input"
            :class="baseClass"
            :value="modelValue"
            v-bind="$attrs"
            @input="
                $emit(
                    'update:modelValue',
                    ($event.currentTarget as HTMLSelectElement).value,
                )
            "
        >
            <option value="" disabled selected hidden>
                {{ placeholder ?? "-" }}
            </option>
            <slot />
        </select>

        <textarea
            v-else-if="type === 'textarea'"
            :id="id"
            ref="input"
            :class="[baseClass, 'min-h-[calc(theme(spacing.1)*7)]']"
            :value="modelValue"
            :placeholder="placeholder ?? ''"
            v-bind="$attrs"
            @input="
                $emit(
                    'update:modelValue',
                    ($event.currentTarget as HTMLTextAreaElement).value,
                )
            "
        />

        <input
            v-else
            :id="id"
            ref="input"
            :placeholder="placeholder ?? ''"
            :class="baseClass"
            :value="modelValue"
            :type="type === 'password' && visible ? 'text' : type"
            v-bind="$attrs"
            @input="
                $emit(
                    'update:modelValue',
                    ($event.currentTarget as HTMLInputElement).value,
                )
            "
        />

        <label
            v-if="label"
            :for="id"
            class="pointer-events-none absolute left-3 top-2 text-xs font-semibold text-2023-teal transition-all peer-disabled:text-gray-500 peer-disabled:opacity-50 peer-placeholder-shown:peer-[&:not(:focus)]:top-4 peer-placeholder-shown:peer-[&:not(:focus)]:text-base"
            :aria-describedby="`${id}-error`"
            >{{ label }}</label
        >

        <span
            v-show="errorMessage"
            :id="`${id}-error`"
            class="mt-2 font-semibold text-2023-red"
            >{{ errorMessage }}</span
        >
    </div>
</template>
