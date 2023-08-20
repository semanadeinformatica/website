<script setup lang="ts">
import type { ButtonHTMLAttributes } from "vue";

withDefaults(
    defineProps<{
        type: ButtonHTMLAttributes["type"];
    }>(),
    { type: "submit" },
);
</script>

<template>
    <button
        :type="type"
        class="relative inline-flex items-center border border-black bg-2023-teal px-4 py-2 font-semibold text-white shadow-2023-orange transition-shadow hover:shadow-md active:shadow-none"
    >
        <slot />
    </button>
</template>
