<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

interface Props {
    href: string;
    as?: "a" | "button";
}

withDefaults(defineProps<Props>(), {
    href: "#",
    as: undefined,
});

const classes =
    "block w-full px-2 py-2 text-sm text-2023-bg underline font-semibold bg-2023-red-dark hover:bg-2023-red focus:outline-none focus:bg-2023-red transition duration-150 ease-in-out";
</script>

<template>
    <div>
        <button v-if="as == 'button'" type="submit" :class="classes">
            <slot />
        </button>

        <a v-else-if="as == 'a'" :href="href" :class="classes">
            <slot />
        </a>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
