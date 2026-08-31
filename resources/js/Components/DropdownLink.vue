<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

interface Props {
    as?: "button" | "a" | undefined;
    href?: string;
    method?: "get" | "post";
    only?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    href: "#",
    as: undefined,
    method: "get",
    only: undefined,
});

const classes =
    "block w-full px-2 py-2 text-md text-white underline font-semibold first:rounded-t-lg last:rounded-b-lg bg-white/20 filter cursor-pointer hover:brightness-90 focus:outline-hidden focus:bg-2025-blue transition duration-150 ease-in-out";
</script>

<template>
    <button v-if="as === 'button'" type="submit" :class="classes">
        <slot />
    </button>

    <a v-else-if="as === 'a'" :href="props.href" :class="classes">
        <slot />
    </a>

    <Link
        v-else
        :href="props.href"
        :method="props.method"
        :class="classes"
        :only="props.only"
        preserve-state
    >
        <slot />
    </Link>
</template>
