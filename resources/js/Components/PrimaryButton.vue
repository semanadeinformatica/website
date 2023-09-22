<script setup lang="ts">
import type { ButtonHTMLAttributes } from "vue";

interface Props {
    type?: ButtonHTMLAttributes["type"];
    color?: string;
    textSize?: string;
}

const buttonColor: Record<string, string> = {
    orange: "bg-2023-orange",
    "teal-dark": "bg-2023-teal-dark",
    "red-dark": "bg-2023-red-dark",
    red: "bg-2023-red",
    teal: "bg-2023-teal",
};

withDefaults(defineProps<Props>(), {
    type: "submit",
    color: "teal",
    textSize: "text-md",
});
</script>

<template>
    <button
        :type="type"
        class="text-md relative inline-flex items-center border border-black px-2 py-2 font-semibold text-white shadow-2023-orange transition-shadow hover:shadow-md active:shadow-none sm:px-4"
        :class="(buttonColor[color], textSize)"
    >
        <slot />
    </button>
</template>
