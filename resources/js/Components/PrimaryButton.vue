<script setup lang="ts">
import type { ButtonHTMLAttributes } from "vue";

interface Props {
  type?: ButtonHTMLAttributes["type"];
  color?: string;
  textSize?: string;
  padding?: string;
}

const buttonColor: Record<string, string> = {
  orange: "bg-2023-orange",
  "teal-dark": "bg-2023-teal-dark",
  "red-dark": "bg-2023-red-dark",
  red: "bg-2023-red",
  teal: "bg-2025-blue",
};

withDefaults(defineProps<Props>(), {
  type: "submit",
  color: "teal",
  textSize: "text-md",
  padding: "",
});
</script>

<template>
  <button
    :type="type"
    class="relative inline-flex items-center justify-center
           rounded-md px-4 py-2 font-semibold text-white
           transition-transform duration-300 hover:scale-105
           [filter:drop-shadow(0_0_0_rgba(0,0,0,0))]
           hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]
           focus:outline-none"
    :class="[buttonColor[color], textSize, padding]"
  >
    <slot />
  </button>
</template>
