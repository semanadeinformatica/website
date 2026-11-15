<script setup lang="ts">
import { computed, type Component } from "vue";
import type ArcadeGame from "@/Types/Arcade";
import { Coins, Gamepad2, Zap } from "@lucide/vue";

interface Props {
    slug: ArcadeGame["slug"] | string;
    name?: string;
    size?: "sm" | "md" | "lg";
}

const props = withDefaults(defineProps<Props>(), {
    name: "",
    size: "md",
});

const imageIcons: Record<string, string> = {
    "bug-sweeper": "/images/arcade/BugSweeper-Icon.png",
    "high-low": "/images/arcade/HighLow-Icon.png",
    mastermind: "/images/arcade/Mastermind-Icon.png",
};

const lucideIcons: Record<string, Component> = {
    "coin-flip": Coins,
    "binary-blitz": Zap,
};

const image = computed(() => imageIcons[props.slug]);
const icon = computed<Component>(() => lucideIcons[props.slug] ?? Gamepad2);

const sizeClasses = {
    sm: "h-9 w-9",
    md: "h-11 w-11",
    lg: "h-12 w-12",
};

const iconSizes = {
    sm: 36,
    md: 44,
    lg: 48,
};
</script>

<template>
    <div
        class="relative flex aspect-square shrink-0 items-center justify-center"
        :class="[sizeClasses[size], image ? '' : 'text-amber-400']"
    >
        <img
            v-if="image"
            :src="image"
            :alt="name"
            class="h-full w-full object-contain brightness-110 contrast-125 drop-shadow-[0_0_10px_rgba(147,70,255,0.45)] saturate-150"
        />
        <component :is="icon" v-else :size="iconSizes[size]" />
    </div>
</template>
