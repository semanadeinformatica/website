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
        ? 'block w-full h-fit py-8 bg-2023-teal lg:text-lg text-2023-bg text-center font-medium focus:outline-none transition duration-150 ease-in-out'
        : 'block w-full h-fit py-8 bg-2023-bg hover:bg-2023-teal focus:bg-2023-teal text-2023-teal text-center hover:text-2023-bg focus:text-2023-bg lg:text-lg font-medium leading-5 focus:outline-none transition duration-150 ease-in-out';
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
