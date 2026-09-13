<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

interface Props {
    href: string;
    active?: boolean;
    as?: "a" | "button";
}

const props = defineProps<Props>();

const isExternalOrAnchor = computed(() => {
    return (
        props.as === "a" ||
        props.href.startsWith("#") ||
        props.href.startsWith("/#") ||
        props.href.startsWith("http")
    );
});

const classes = computed(() => {
    return props.active ? "pill-item pill-item-active" : "pill-item";
});
</script>

<template>
    <a v-if="isExternalOrAnchor" :href="href" :class="classes">
        <slot />
    </a>
    <button v-else-if="as === 'button'" type="button" :class="classes">
        <slot />
    </button>
    <Link v-else :href="href" :class="classes">
        <slot />
    </Link>
</template>
