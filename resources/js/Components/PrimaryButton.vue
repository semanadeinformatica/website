<script setup lang="ts">
import type { ButtonHTMLAttributes } from "vue";

interface Props {
    type?: ButtonHTMLAttributes["type"];
    color?: "primary" | "secondary" | "gradient" | "danger";
    textSize?: string;
    padding?: string;
    disabled?: boolean;
}

const buttonColor: Record<string, string> = {
    primary:
        "bg-sinf-primary/80 hover:bg-sinf-primary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.4)]",
    secondary:
        "bg-sinf-secondary/80 hover:bg-sinf-secondary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(106,0,255,0.4)]",
    gradient:
        "bg-linear-to-r from-sinf-primary/90 to-sinf-secondary/90 hover:from-sinf-primary hover:to-sinf-secondary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.35)]",
    danger: "bg-red-500/80 hover:bg-red-500 text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(239,68,68,0.4)]",
};

withDefaults(defineProps<Props>(), {
    type: "submit",
    color: "primary",
    textSize: "text-sm sm:text-base",
    padding: "px-6 py-2.5",
    disabled: false,
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="pill-container pill-item cursor-pointer justify-center gap-1.5 font-semibold transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
        :class="[buttonColor[color] ?? buttonColor.primary, textSize, padding]"
    >
        <slot />
    </button>
</template>
