<script setup lang="ts">
import { AlertCircle } from "@lucide/vue";
import { nextTick, onMounted, ref, watch } from "vue";

interface Props {
    modelValue?: string;
    length?: number;
    id?: string;
    name?: string;
    label?: string;
    errorMessage?: string;
    autofocus?: boolean;
    disabled?: boolean;
    center?: boolean;
}

interface Emits {
    (event: "update:modelValue", value: string): void;
    (event: "complete", value: string): void;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: "",
    length: 6,
    id: "code-input",
    name: "code",
    label: undefined,
    errorMessage: undefined,
    autofocus: true,
    disabled: false,
    center: true,
});

const emit = defineEmits<Emits>();

const digits = ref<string[]>(Array(props.length).fill(""));
const inputRefs = ref<HTMLInputElement[]>([]);

watch(
    () => props.modelValue,
    (newVal) => {
        const val = newVal ?? "";
        for (let i = 0; i < props.length; i++) {
            digits.value[i] = val[i] ?? "";
        }
    },
    { immediate: true },
);

const updateValue = () => {
    const joined = digits.value.join("");
    emit("update:modelValue", joined);
    if (joined.length === props.length && !digits.value.includes("")) {
        emit("complete", joined);
    }
};

const handleInput = (index: number, event: Event) => {
    const input = event.target as HTMLInputElement;
    const val = input.value;

    if (val.length > 1) {
        const cleaned = val.replace(/\D/g, "").slice(0, props.length);
        const startIdx = cleaned.length >= props.length ? 0 : index;
        for (let i = 0; i < props.length; i++) {
            if (i >= startIdx && i - startIdx < cleaned.length) {
                digits.value[i] = cleaned[i - startIdx];
            }
        }
        updateValue();
        const nextIndex = Math.min(startIdx + cleaned.length, props.length - 1);
        nextTick(() => {
            inputRefs.value[nextIndex]?.focus();
        });
        return;
    }

    const char = val.replace(/\D/g, "");
    digits.value[index] = char;
    updateValue();

    if (char && index < props.length - 1) {
        nextTick(() => {
            inputRefs.value[index + 1]?.focus();
            inputRefs.value[index + 1]?.select();
        });
    }
};

const handleKeyDown = (index: number, event: KeyboardEvent) => {
    if (event.key === "Backspace") {
        if (!digits.value[index] && index > 0) {
            event.preventDefault();
            digits.value[index - 1] = "";
            updateValue();
            inputRefs.value[index - 1]?.focus();
        } else {
            digits.value[index] = "";
            updateValue();
        }
    } else if (event.key === "ArrowLeft" && index > 0) {
        event.preventDefault();
        inputRefs.value[index - 1]?.focus();
        inputRefs.value[index - 1]?.select();
    } else if (event.key === "ArrowRight" && index < props.length - 1) {
        event.preventDefault();
        inputRefs.value[index + 1]?.focus();
        inputRefs.value[index + 1]?.select();
    }
};

const handlePaste = (index: number, event: ClipboardEvent) => {
    event.preventDefault();
    const text = event.clipboardData?.getData("text") ?? "";
    const cleaned = text.replace(/\D/g, "").slice(0, props.length);
    if (!cleaned) return;

    for (let i = 0; i < props.length; i++) {
        digits.value[i] = cleaned[i] ?? "";
    }
    updateValue();

    const targetIndex = Math.min(cleaned.length, props.length - 1);
    inputRefs.value[targetIndex]?.focus();
};

const handleFocus = (index: number) => {
    inputRefs.value[index]?.select();
};

onMounted(() => {
    if (props.autofocus && !props.disabled) {
        nextTick(() => {
            const firstEmptyIndex = digits.value.findIndex((d) => !d);
            const targetIndex = firstEmptyIndex === -1 ? 0 : firstEmptyIndex;
            inputRefs.value[targetIndex]?.focus();
        });
    }
});

const focus = (index = 0) => {
    inputRefs.value[index]?.focus();
};

defineExpose({ focus });
</script>

<template>
    <div
        class="relative flex flex-col self-stretch"
        :class="center ? 'items-center' : 'items-start'"
    >
        <label
            v-if="label"
            :for="`${id}-0`"
            class="block text-xs font-medium text-neutral-400"
            :class="center ? 'mb-2 text-center' : 'mb-1.5 ml-3.5 text-left'"
        >
            {{ label }}
        </label>

        <input
            type="hidden"
            :name="name ?? id"
            :value="modelValue"
            autocomplete="one-time-code"
        />

        <div
            class="flex items-center gap-2 sm:gap-3"
            :class="center ? 'justify-center' : 'justify-start'"
        >
            <template v-for="(_, index) in length" :key="index">
                <span
                    v-if="index === Math.floor(length / 2)"
                    class="px-0.5 font-mono text-lg text-neutral-500 select-none"
                >
                    –
                </span>

                <input
                    :id="`${id}-${index}`"
                    ref="inputRefs"
                    v-model="digits[index]"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    :maxlength="index === 0 ? 16 : 1"
                    :disabled="disabled"
                    :autocomplete="index === 0 ? 'one-time-code' : 'off'"
                    class="h-12 w-10 rounded-2xl border bg-black/50 text-center font-mono text-xl font-bold text-white shadow-(--shadow-pill-inset) backdrop-blur-md transition-all duration-200 select-all focus:bg-black/70 focus:outline-none sm:h-14 sm:w-12 sm:text-2xl"
                    :class="[
                        errorMessage
                            ? 'border-red-500/50 text-red-300 focus:border-red-500/80 focus:ring-1 focus:ring-red-500/30'
                            : digits[index]
                              ? 'border-white/25 bg-white/10'
                              : 'border-white/10 hover:border-white/20 focus:border-white/30 focus:ring-1 focus:ring-white/20',
                        disabled ? 'cursor-not-allowed opacity-40' : '',
                    ]"
                    @input="handleInput(index, $event)"
                    @change="handleInput(index, $event)"
                    @keydown="handleKeyDown(index, $event)"
                    @paste="handlePaste(index, $event)"
                    @focus="handleFocus(index)"
                />
            </template>
        </div>

        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
        >
            <div
                v-if="errorMessage"
                class="flex items-center gap-1.5 text-xs font-medium text-red-400"
                :class="
                    center
                        ? 'mt-2 justify-center'
                        : 'mt-1.5 ml-3.5 justify-start'
                "
            >
                <AlertCircle :size="14" class="shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>
        </transition>
    </div>
</template>
