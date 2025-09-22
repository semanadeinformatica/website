<script setup lang="ts" generic="">
import { OhVueIcon } from "oh-vue-icons";
import { onMounted, ref, type InputHTMLAttributes, computed } from "vue";

interface BaseProps {
    label?: string;
    id?: string;
    placeholder?: string;
    errorMessage?: string;
}

interface InputProps extends BaseProps {
    type: InputHTMLAttributes["type"];
    modelValue: string;
}

interface BaseSelectProps extends BaseProps {
    type: "select";
}

interface MultiSelectProps extends BaseSelectProps {
    multiple: true;
    modelValue: string[];
}

interface SingleSelectProps extends BaseSelectProps {
    multiple?: false;
    modelValue: string;
}

type SelectProps = MultiSelectProps | SingleSelectProps;

interface TextAreaProps extends BaseProps {
    type: "textarea";
    modelValue: string;
}

type Props = InputProps | SelectProps | TextAreaProps;
const isSelect = (p: Props): p is SelectProps => {
    return p.type === "select";
};

interface Emits {
    (event: "update:modelValue", value: string | string[]): void;
}

// Need to instantiate 'props' here otherwise TS would not correctly infer the types I wanted from the '$props' variable. - Nuno Pereira
const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const value = computed({
    get: () => props.modelValue,
    set: (v) => emit("update:modelValue", v),
});

const input = ref<
    HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement | null
>(null);

onMounted(() => {
    if (input.value?.hasAttribute("autofocus")) input.value.focus();
});

defineExpose({ focus: () => input.value?.focus() });

defineOptions({ inheritAttrs: false });

const baseClass =
    "peer border border-white bg-white shadow-md shadow-2025-blue px-3 pb-2 pt-6 disabled:opacity-50 disabled:text-gray-500 disabled:shadow-gray-500 disabled:shadow-md";

const visible = ref(false);
</script>

<template>
    <div class="relative flex flex-col items-stretch self-stretch">
        <select
            v-if="isSelect(props)"
            :id="id"
            ref="input"
            v-model="value"
            :class="baseClass"
            v-bind="$attrs"
            :multiple="props.multiple"
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
            v-model="value"
            :class="[baseClass, 'min-h-[calc(theme(spacing.1)*7)]']"
            :placeholder="placeholder ?? ''"
            v-bind="$attrs"
        />

        <input
            v-else
            :id="id"
            ref="input"
            v-model="value"
            :placeholder="placeholder ?? ''"
            :class="[baseClass, type === 'password' ? 'pr-12' : '']"
            :type="type === 'password' && visible ? 'text' : type"
            v-bind="$attrs"
        />

        <label
            v-if="label"
            :for="id"
            class="pointer-events-none absolute left-3 top-2 text-xs font-semibold text-2023-teal transition-all peer-disabled:text-gray-500 peer-disabled:opacity-50 peer-placeholder-shown:peer-[&:not(:focus)]:top-4 peer-placeholder-shown:peer-[&:not(:focus)]:text-base"
            :aria-describedby="`${id}-error`"
            >{{ label }}</label
        >

        <button
            v-if="type === 'password'"
            type="button"
            class="absolute right-2 top-2 p-2 leading-4 text-2023-teal peer-disabled:text-gray-500 peer-disabled:opacity-50"
            @click="visible = !visible"
        >
            <OhVueIcon
                :name="visible ? 'io-eye' : 'io-eye-off'"
                class="h-6 w-6"
            />
        </button>

        <span
            v-show="errorMessage"
            :id="`${id}-error`"
            class="mt-2 font-semibold text-2023-red"
            >{{ errorMessage }}</span
        >
    </div>
</template>
