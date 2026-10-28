<script setup lang="ts">
import { computed } from "vue";
import { Minus, Plus, AlertCircle } from "@lucide/vue";

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

const step = computed(() => Math.max(1, Math.floor(props.min / 2) || 5));

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
    emit("update:modelValue", Math.round(val));
}

function handleInput(event: Event) {
    const target = event.target as HTMLInputElement;
    const num = parseInt(target.value, 10);
    if (!isNaN(num)) {
        updateValue(num);
    }
}

function decrement() {
    updateValue(Math.max(props.min, props.modelValue - step.value));
}

function increment() {
    updateValue(props.modelValue + step.value);
}

function setMin() {
    updateValue(props.min);
}

function setMax() {
    const effectiveMax = Math.min(props.max, props.balance);
    updateValue(effectiveMax > props.min ? effectiveMax : props.max);
}
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
                'relative flex items-center rounded-2xl border bg-black/40 p-1.5 backdrop-blur-sm transition-colors duration-200',
                hasError
                    ? 'border-red-500/50 ring-1 ring-red-500/20'
                    : 'border-white/10 focus-within:border-amber-400/50',
                disabled ? 'pointer-events-none opacity-50' : '',
            ]"
        >
            <!-- Decrement Button -->
            <button
                type="button"
                :disabled="disabled || modelValue <= min"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 text-neutral-300 transition-colors hover:bg-white/10 active:scale-95 disabled:pointer-events-none disabled:opacity-30"
                @click="decrement"
            >
                <Minus :size="16" />
            </button>

            <!-- Number Input -->
            <input
                type="number"
                :value="modelValue"
                :min="min"
                :max="max"
                :disabled="disabled"
                class="w-full [appearance:textfield] border-0 bg-transparent text-center font-mono text-lg font-bold text-white focus:ring-0 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                @input="handleInput"
            />

            <!-- Increment Button -->
            <button
                type="button"
                :disabled="disabled || modelValue >= max"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 text-neutral-300 transition-colors hover:bg-white/10 active:scale-95 disabled:pointer-events-none disabled:opacity-30"
                @click="increment"
            >
                <Plus :size="16" />
            </button>
        </div>

        <!-- Quick actions & validation message -->
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5">
                <button
                    type="button"
                    :disabled="disabled"
                    class="rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-medium text-neutral-400 transition-colors hover:bg-white/10 hover:text-white disabled:opacity-40"
                    @click="setMin"
                >
                    Mínimo
                </button>
                <button
                    type="button"
                    :disabled="disabled"
                    class="rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-medium text-neutral-400 transition-colors hover:bg-white/10 hover:text-white disabled:opacity-40"
                    @click="setMax"
                >
                    Máximo
                </button>
            </div>

            <!-- Error Feedback -->
            <span
                v-if="hasError"
                class="flex items-center gap-1 text-[11px] font-medium text-red-400"
            >
                <AlertCircle :size="12" />
                {{ errorMessage }}
            </span>
        </div>
    </div>
</template>
