<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

interface Props {
    active?: boolean;
    href: string;
    as?: "button" | "a";
    method?: "get" | "post";
}

const props = defineProps<Props>();

const isExternalOrAnchor = computed(() => {
    if (props.method === "post" || props.as === "button") return false;
    return (
        props.as === "a" ||
        props.href.startsWith("#") ||
        props.href.startsWith("/#")
    );
});

const classes = computed(() => {
    return props.active
        ? "flex w-full items-center justify-between rounded-xl bg-white/[0.12] px-3.5 py-3 text-sm font-semibold text-white shadow-[inset_0_1px_0_0_rgba(255,255,255,0.2)] transition-all duration-150 active:scale-[0.99] focus:outline-none"
        : "flex w-full items-center justify-between rounded-xl px-3.5 py-3 text-sm font-medium text-neutral-300 transition-all duration-150 hover:bg-white/[0.08] hover:text-white active:scale-[0.99] focus:outline-none";
});
</script>

<template>
    <a v-if="isExternalOrAnchor" :href="href" :class="classes">
        <slot />
    </a>
    <Link v-else :href="href" :as="as" :method="method" :class="classes">
        <slot />
    </Link>
</template>
