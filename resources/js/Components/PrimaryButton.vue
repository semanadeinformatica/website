<script setup lang="ts">
import { computed, ref, useAttrs, type ButtonHTMLAttributes } from "vue";

interface Props {
    type?: ButtonHTMLAttributes["type"];
    color?: "pill" | "primary" | "secondary" | "gradient" | "danger";
    textSize?: string;
    padding?: string;
    disabled?: boolean;
    fullWidth?: boolean;
}

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const props = withDefaults(defineProps<Props>(), {
    type: "submit",
    color: "pill",
    textSize: "text-xs sm:text-sm",
    padding: "px-6 py-2",
    disabled: false,
    fullWidth: false,
});

const buttonRef = ref<HTMLButtonElement | null>(null);

const isFullWidth = computed(() => {
    return (
        props.fullWidth ||
        Boolean(attrs.class && String(attrs.class).includes("w-full"))
    );
});

const onContainerClick = (event: MouseEvent) => {
    if (
        event.target !== buttonRef.value &&
        buttonRef.value &&
        !props.disabled
    ) {
        buttonRef.value.click();
    }
};

const buttonColor: Record<string, string> = {
    pill: "pill-item-active hover:bg-white/20 text-white",
    primary:
        "bg-sinf-primary/80 hover:bg-sinf-primary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.4)]",
    secondary:
        "bg-sinf-secondary/80 hover:bg-sinf-secondary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(106,0,255,0.4)]",
    gradient:
        "bg-linear-to-r from-sinf-primary/90 to-sinf-secondary/90 hover:from-sinf-primary hover:to-sinf-secondary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.35)]",
    danger: "bg-red-500/80 hover:bg-red-500 text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(239,68,68,0.4)]",
};
</script>

<template>
    <div
        class="pill-container justify-center p-1.5 transition-all duration-200 select-none"
        :class="[
            isFullWidth ? 'flex w-full' : 'inline-flex',
            disabled ? 'cursor-not-allowed opacity-40' : 'cursor-pointer',
            attrs.class,
        ]"
        @click="onContainerClick"
    >
        <button
            ref="buttonRef"
            v-bind="{ ...attrs, class: undefined }"
            :type="type"
            :disabled="disabled"
            class="pill-item cursor-pointer justify-center gap-2 font-medium transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
            :class="[
                isFullWidth ? 'w-full' : '',
                buttonColor[color] ?? buttonColor.pill,
                textSize,
                padding,
            ]"
        >
            <slot />
        </button>
    </div>
</template>
