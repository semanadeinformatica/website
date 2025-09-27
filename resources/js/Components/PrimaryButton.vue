<script setup lang="ts">
import type { ButtonHTMLAttributes } from "vue";

interface Props {
    type?: ButtonHTMLAttributes["type"];
    color?: string;
    shadow?: string;
    textSize?: string;
    padding?: string;
}

const buttonColor: Record<string, string> = {
    orange: "bg-2023-orange",
    "teal-dark": "bg-2023-teal-dark",
    "red-dark": "bg-2023-red-dark",
    red: "bg-2023-red",
    teal: "bg-2023-teal",
};

const shadowColor: Record<string, string> = {
    orange: "shadow-black/80",
    "teal-dark": "shadow-2023-teal-dark",
    "red-dark": "shadow-2023-red-dark",
    red: "shadow-2023-red",
    teal: "shadow-2023-teal",
};

withDefaults(defineProps<Props>(), {
    type: "submit",
    color: "teal",
    shadow: "orange",
    textSize: "text-md",
    padding: "",
});
</script>

<template>
    <button
        :type="type"
        class="text-md relative inline-flex items-center border border-white rounded-md shadow shadow-black/80 px-2 py-2 font-semibold text-white transition-shadow hover:shadow-md active:shadow-none"
        :class="[buttonColor[color], shadowColor[shadow], textSize, padding]"
    >
        <slot />
    </button>
</template>
