<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

interface Props {
    as?: "button" | "a" | undefined;
    href?: string;
    method?: "get" | "post";
    only?: string[];
    danger?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    href: "#",
    as: undefined,
    method: "get",
    only: undefined,
    danger: false,
});

const isExternalOrAnchor = computed(() => {
    if (props.method === "post" || props.as === "button") return false;
    return (
        props.as === "a" ||
        props.href.startsWith("#") ||
        props.href.startsWith("/#")
    );
});

const classes = computed(() => {
    return props.danger || props.method === "post"
        ? "group flex w-full cursor-pointer items-center justify-between gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium text-red-400 transition-colors hover:bg-red-500/10 hover:text-red-300 focus:outline-none"
        : "group flex w-full cursor-pointer items-center justify-between gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium text-neutral-300 transition-colors hover:bg-white/[0.08] hover:text-white focus:outline-none";
});
</script>

<template>
    <a
        v-if="isExternalOrAnchor"
        :href="props.href"
        :class="classes"
        target="_blank"
        rel="noopener noreferrer"
    >
        <slot />
    </a>

    <Link
        v-else
        :href="props.href"
        :as="props.as"
        :method="props.method"
        :class="classes"
        :only="props.only"
    >
        <slot />
    </Link>
</template>
