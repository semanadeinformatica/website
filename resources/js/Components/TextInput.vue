<script setup lang="ts">
import { Eye, EyeOff, AlertCircle } from "@lucide/vue";
import { onMounted, ref, type InputHTMLAttributes, computed } from "vue";

interface BaseProps {
    label?: string;
    id?: string;
    placeholder?: string;
    errorMessage?: string;
}

interface InputProps extends BaseProps {
    type?: InputHTMLAttributes["type"];
    modelValue: string | number;
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
    (event: "update:modelValue", value: string | number | string[]): void;
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

const visible = ref(false);
</script>

<template>
    <div class="relative flex flex-col items-stretch self-stretch">
        <label
            v-if="label"
            :for="id"
            class="mb-1.5 ml-3.5 block text-xs font-medium text-neutral-400"
        >
            {{ label }}
        </label>

        <div
            v-if="isSelect(props)"
            class="pill-container w-full px-4 py-2 transition-all duration-200 focus-within:border-white/20 focus-within:bg-black/65"
            :class="
                errorMessage
                    ? 'border-red-500/50 focus-within:border-red-500/60 focus-within:ring-1 focus-within:ring-red-500/30'
                    : ''
            "
        >
            <select
                :id="id"
                ref="input"
                v-model="value"
                class="w-full cursor-pointer border-0 bg-transparent text-sm text-white shadow-none outline-none focus:border-transparent focus:shadow-none focus:ring-0 focus:outline-none"
                v-bind="$attrs"
                :multiple="props.multiple"
            >
                <option
                    value=""
                    disabled
                    selected
                    hidden
                    class="bg-neutral-900 text-neutral-400"
                >
                    {{ placeholder ?? "-" }}
                </option>
                <slot />
            </select>
        </div>

        <div
            v-else-if="type === 'textarea'"
            class="w-full rounded-3xl border border-white/8 bg-black/50 p-3.5 shadow-(--shadow-pill-inset) backdrop-blur-md transition-all duration-200 focus-within:border-white/20 focus-within:bg-black/65"
            :class="
                errorMessage
                    ? 'border-red-500/50 focus-within:border-red-500/60 focus-within:ring-1 focus-within:ring-red-500/30'
                    : ''
            "
        >
            <textarea
                :id="id"
                ref="input"
                v-model="value"
                class="min-h-24 w-full resize-y border-0 bg-transparent text-sm text-white placeholder-neutral-500 shadow-none outline-none focus:border-transparent focus:shadow-none focus:ring-0 focus:outline-none"
                :placeholder="placeholder ?? ''"
                v-bind="$attrs"
            />
        </div>

        <div
            v-else
            class="pill-container w-full px-4 py-2 transition-all duration-200 focus-within:border-white/20 focus-within:bg-black/65"
            :class="
                errorMessage
                    ? 'border-red-500/50 focus-within:border-red-500/60 focus-within:ring-1 focus-within:ring-red-500/30'
                    : ''
            "
        >
            <input
                :id="id"
                ref="input"
                v-model="value"
                :placeholder="placeholder ?? ''"
                :type="type === 'password' && visible ? 'text' : type"
                class="w-full border-0 bg-transparent text-sm text-white placeholder-neutral-500 shadow-none outline-none focus:border-transparent focus:shadow-none focus:ring-0 focus:outline-none"
                v-bind="$attrs"
            />

            <button
                v-if="type === 'password'"
                type="button"
                tabindex="-1"
                class="ml-2 flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center rounded-full text-neutral-400 transition-colors hover:bg-white/10 hover:text-white focus:outline-none"
                :aria-label="visible ? 'Ocultar password' : 'Ver password'"
                @click="visible = !visible"
            >
                <EyeOff v-if="visible" :size="16" />
                <Eye v-else :size="16" />
            </button>
        </div>

        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
        >
            <div
                v-if="errorMessage"
                :id="`${id}-error`"
                class="mt-1.5 ml-3.5 flex items-center gap-1.5 text-xs font-medium text-red-400"
            >
                <AlertCircle :size="14" class="shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>
        </transition>
    </div>
</template>

<style scoped>
input,
select,
textarea {
    border: 0 !important;
    outline: none !important;
    box-shadow: none !important;
}

input:focus,
select:focus,
textarea:focus {
    border-color: transparent !important;
    outline: none !important;
    box-shadow: none !important;
    --tw-ring-shadow: none !important;
    --tw-ring-offset-shadow: none !important;
}
</style>
