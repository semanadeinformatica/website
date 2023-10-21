<script setup lang="ts">
import type Slot from "@/Types/Slot";
import { computed } from "vue";

const { sticker } = defineProps<{
    active?: boolean;
    color: string;
    sticker: Slot;
}>();

const bgColor: Record<string, string> = {
    orange: "bg-2023-orange",
    "teal-dark": "bg-2023-teal-dark",
    "red-dark": "bg-2023-red-dark",
    red: "bg-2023-red",
    teal: "bg-2023-teal",
};

const completeness = computed(() => {
    return Math.min(1, (sticker.completed_count ?? 0) / sticker.total_quests);
});
</script>

<template>
    <div
        class="sticker-clip-path group relative flex h-56 w-56 basis-0 justify-center overflow-hidden"
        :class="bgColor[color]"
    >
        <img
            class="sticker-clip-path w-52 h-52 object-cover m-auto transition-all duration-500 group-hover:blur"
            :src="sticker.image_slot_url ?? ''"
            :alt="sticker.name"
        />
        <div
            class="absolute inset-0 mix-blend-saturation"
            :style="`background: conic-gradient(transparent ${completeness}turn, black ${completeness}turn 1turn)`"
        ></div>
        <div
            class="sticker-clip-path absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 p-10 text-center text-white opacity-0 transition-all duration-500 group-hover:opacity-100"
        >
            {{ sticker.name }}
        </div>
    </div>
</template>

<style>
.sticker-clip-path {
    clip-path: url(#clip-path-svg);
    object-fit: cover;
}
</style>
