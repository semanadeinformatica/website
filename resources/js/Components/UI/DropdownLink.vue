<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

interface Props {
    as?: "button" | "a" | undefined;
    href?: string;
    method?: "get" | "post";
    only?: string[];
    danger?: boolean;
    active?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    href: "#",
    as: undefined,
    method: "get",
    only: undefined,
    danger: false,
    active: false,
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
    if (props.danger || props.method === "post") {
        return "group flex w-full cursor-pointer items-center justify-between gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium text-red-400 transition-all duration-150 hover:bg-red-500/10 hover:text-red-300 active:scale-[0.98] focus:outline-none";
    }
    if (props.active) {
        return "group flex w-full cursor-pointer items-center justify-between gap-2.5 rounded-xl bg-white/14 px-3 py-2 text-left text-xs font-semibold text-white shadow-pill-active transition-all duration-150 active:scale-[0.98] focus:outline-none";
    }
    return "group flex w-full cursor-pointer items-center justify-between gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium text-neutral-300 transition-all duration-150 hover:bg-white/8 hover:text-white active:scale-[0.98] focus:outline-none";
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
