<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

interface Props {
    active?: boolean;
    href: string;
    as?: "button";
}

const props = defineProps<Props>();

const classes = computed(() => {
    return props.active
        ? "flex flex-col w-fit py-4 bg-transparent underline text-white text-center font-medium focus:outline-none transition duration-150 ease-in-out"
        : "flex flex-col w-fit py-4 bg-transparent hover:underline focus:underline text-white text-center font-medium leading-5 focus:outline-none transition duration-150 ease-in-out";
});
</script>

<template>
    <button v-if="as == 'button'" :class="classes">
        <slot />
    </button>

    <Link v-else :href="href" :class="classes">
        <slot />
    </Link>
</template>
