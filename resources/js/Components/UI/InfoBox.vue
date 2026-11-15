<script setup lang="ts">
import { computed } from "vue";

type Variant = "info" | "warning" | "danger";

interface Props {
    variant?: Variant;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: "info",
    title: undefined,
});

const config = computed(() => {
    switch (props.variant) {
        case "warning":
            return {
                wrapper: "border-amber-500/25 bg-amber-500/10",
                title: "text-amber-100",
                text: "text-amber-100/80",
            };
        case "danger":
            return {
                wrapper: "border-red-500/30 bg-red-500/10",
                title: "text-red-100",
                text: "text-red-200/80",
            };
        case "info":
        default:
            return {
                wrapper: "border-white/10 bg-white/[0.03]",
                title: "text-white",
                text: "text-neutral-400",
            };
    }
});
</script>

<template>
    <div class="rounded-2xl border p-3.5 sm:p-4" :class="config.wrapper">
        <p
            v-if="title"
            class="text-xs font-semibold tracking-wide"
            :class="config.title"
        >
            {{ title }}
        </p>
        <div class="text-xs leading-relaxed" :class="config.text">
            <slot />
        </div>
    </div>
</template>
