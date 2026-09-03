<script setup lang="ts">
import type { ButtonHTMLAttributes } from "vue";

interface Props {
    type?: ButtonHTMLAttributes["type"];
    color?: string;
    textSize?: string;
    padding?: string;
    disabled?: boolean;
}

const buttonColor: Record<string, string> = {
    crimson:
        "bg-sinf-crimson/80 hover:bg-sinf-crimson text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.4)]",
    purple: "bg-sinf-purple/80 hover:bg-sinf-purple text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(106,0,255,0.4)]",
    gradient:
        "bg-linear-to-r from-sinf-crimson/90 to-sinf-purple/90 hover:from-sinf-crimson hover:to-sinf-purple text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.35)]",
    teal: "bg-sinf-crimson/80 hover:bg-sinf-crimson text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.4)]",
    orange: "bg-sinf-crimson/80 hover:bg-sinf-crimson text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.4)]",
    red: "bg-sinf-crimson/80 hover:bg-sinf-crimson text-white hover:border-white/20",
    "red-dark":
        "bg-sinf-crimson-dark/90 hover:bg-sinf-crimson text-white hover:border-white/20",
    "teal-dark":
        "bg-sinf-purple-dark/90 hover:bg-sinf-purple text-white hover:border-white/20",
};

withDefaults(defineProps<Props>(), {
    type: "submit",
    color: "crimson",
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
        :class="[buttonColor[color] ?? buttonColor.crimson, textSize, padding]"
    >
        <slot />
    </button>
</template>
