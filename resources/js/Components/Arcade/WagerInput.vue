<script setup lang="ts">
import { computed, ref, watch, onBeforeUnmount } from "vue";
import { Minus, Plus, AlertCircle, ChevronsLeft, ChevronsRight } from "@lucide/vue";

interface Props {
    modelValue: number;
    min: number;
    max: number;
    balance: number;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: number): void;
}>();

const step = computed(() => Math.max(1, Math.floor(props.min / 10)));

const displayValue = ref<string>(String(props.modelValue));

watch(
    () => props.modelValue,
    (newVal) => {
        if (parseInt(displayValue.value, 10) !== newVal) {
            displayValue.value = String(newVal);
        }
    },
);

const isOverBalance = computed(() => props.modelValue > props.balance);
const isBelowMin = computed(() => props.modelValue < props.min);
const isAboveMax = computed(() => props.modelValue > props.max);
const hasError = computed(
    () => isOverBalance.value || isBelowMin.value || isAboveMax.value,
);

const errorMessage = computed(() => {
    if (isOverBalance.value) return "Saldo insuficiente";
    if (isBelowMin.value) return `Aposta mínima: ${props.min} pts`;
    if (isAboveMax.value) return `Aposta máxima: ${props.max} pts`;
    return "";
});

function updateValue(val: number) {
    if (props.disabled) return;
    const rounded = Math.round(val);
    emit("update:modelValue", rounded);
    displayValue.value = String(rounded);
}

function handleInput(event: Event) {
    const target = event.target as HTMLInputElement;
    const cleaned = target.value.replace(/[^0-9]/g, "");
    displayValue.value = cleaned;

    if (cleaned === "") {
        emit("update:modelValue", 0);
        return;
    }

    const num = parseInt(cleaned, 10);
    if (!isNaN(num)) {
        emit("update:modelValue", num);
    }
}

function handleBlur() {
    const num = parseInt(displayValue.value, 10);
    if (isNaN(num) || num < props.min) {
        updateValue(props.min);
    } else if (num > props.max) {
        updateValue(props.max);
    } else {
        updateValue(num);
    }
}

function decrement() {
    updateValue(Math.max(props.min, props.modelValue - step.value));
}

function increment() {
    updateValue(Math.min(props.max, props.modelValue + step.value));
}

function setMin() {
    updateValue(props.min);
}

function setMax() {
    const effectiveMax = Math.min(props.max, props.balance);
    updateValue(effectiveMax > props.min ? effectiveMax : props.max);
}

// Continuous hold-to-repeat for + and - buttons
let repeatTimeout: ReturnType<typeof setTimeout> | null = null;
let repeatInterval: ReturnType<typeof setInterval> | null = null;

function startRepeat(action: () => void) {
    if (props.disabled) return;
    stopRepeat();
    action();

    repeatTimeout = setTimeout(() => {
        repeatInterval = setInterval(() => {
            action();
        }, 60);
    }, 320);
}

function stopRepeat() {
    if (repeatTimeout) {
        clearTimeout(repeatTimeout);
        repeatTimeout = null;
    }
    if (repeatInterval) {
        clearInterval(repeatInterval);
        repeatInterval = null;
    }
}

onBeforeUnmount(() => {
    stopRepeat();
});
</script>

<template>
    <div class="w-full space-y-2">
        <div class="flex items-center justify-between text-xs text-neutral-400">
            <span>Aposta</span>
            <span class="flex items-center gap-1 font-mono">
                Saldo:
                <strong class="text-white">{{ balance }}</strong>
                pts
            </span>
        </div>

        <div
            :class="[
                'relative flex items-center gap-1 rounded-2xl border bg-black/40 p-1.5 backdrop-blur-sm transition-colors duration-200',
                hasError
                    ? 'border-red-500/50 ring-1 ring-red-500/20'
                    : 'border-white/10 focus-within:border-amber-400/50',
                disabled ? 'pointer-events-none opacity-50' : '',
            ]"
        >
            <!-- Jump to Min Button (<<) -->
            <button
                type="button"
                aria-label="Aposta mínima"
                title="Mínimo"
                :disabled="disabled || modelValue <= min"
                class="flex h-10 w-9 shrink-0 items-center justify-center rounded-xl bg-white/5 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white active:scale-95 disabled:pointer-events-none disabled:opacity-25"
                @click="setMin"
            >
                <ChevronsLeft :size="16" />
            </button>

            <!-- Decrement Button (-) with hold-to-repeat -->
            <button
                type="button"
                aria-label="Diminuir aposta"
                :disabled="disabled || modelValue <= min"
                class="flex h-10 w-9 shrink-0 items-center justify-center rounded-xl bg-white/5 text-neutral-300 transition-colors hover:bg-white/10 hover:text-white active:scale-95 disabled:pointer-events-none disabled:opacity-30"
                @pointerdown.prevent="startRepeat(decrement)"
                @pointerup="stopRepeat"
                @pointerleave="stopRepeat"
                @pointercancel="stopRepeat"
                @contextmenu.prevent
            >
                <Minus :size="16" />
            </button>

            <!-- Numeric Input -->
            <input
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                aria-label="Aposta"
                :value="displayValue"
                :disabled="disabled"
                class="min-w-0 flex-1 border-0 bg-transparent text-center font-mono text-lg font-bold text-white focus:ring-0 focus:outline-none"
                @input="handleInput"
                @blur="handleBlur"
                @keydown.enter="handleBlur"
            />

            <!-- Increment Button (+) with hold-to-repeat -->
            <button
                type="button"
                aria-label="Aumentar aposta"
                :disabled="disabled || modelValue >= max"
                class="flex h-10 w-9 shrink-0 items-center justify-center rounded-xl bg-white/5 text-neutral-300 transition-colors hover:bg-white/10 hover:text-white active:scale-95 disabled:pointer-events-none disabled:opacity-30"
                @pointerdown.prevent="startRepeat(increment)"
                @pointerup="stopRepeat"
                @pointerleave="stopRepeat"
                @pointercancel="stopRepeat"
                @contextmenu.prevent
            >
                <Plus :size="16" />
            </button>

            <!-- Jump to Max Button (>>) -->
            <button
                type="button"
                aria-label="Aposta máxima"
                title="Máximo"
                :disabled="disabled || modelValue >= max"
                class="flex h-10 w-9 shrink-0 items-center justify-center rounded-xl bg-white/5 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white active:scale-95 disabled:pointer-events-none disabled:opacity-25"
                @click="setMax"
            >
                <ChevronsRight :size="16" />
            </button>
        </div>

        <!-- Validation error message if any -->
        <div v-if="hasError" class="flex items-center justify-end">
            <span class="flex items-center gap-1 text-[11px] font-medium text-red-400">
                <AlertCircle :size="12" />
                {{ errorMessage }}
            </span>
        </div>
    </div>
</template>
