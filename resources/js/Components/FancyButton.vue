<script setup lang="ts">
import type { ButtonHTMLAttributes } from "vue";

interface Props {
    type?: ButtonHTMLAttributes["type"];
}

withDefaults(defineProps<Props>(), { type: "submit" });
</script>

<template>
    <button
        :type="type"
        class="group relative inline-flex w-fit items-center border border-black bg-2023-red-dark p-2 text-xl font-bold text-white shadow-2023-teal transition-all duration-500 hover:text-2023-bg hover:shadow-md active:shadow-none sm:bg-2023-bg sm:px-10 sm:py-5 sm:text-5xl sm:text-2023-red-dark"
    >
        <span
            className="w-0 h-0 bg-2023-red-dark absolute top-0 left-0 ease-out duration-500 transition-all group-hover:w-full group-hover:h-full -z-1"
        ></span>
        <span
            className="w-full transition-colors duration-300 ease-in-out z-10"
        >
            <slot />
        </span>
    </button>
</template>
