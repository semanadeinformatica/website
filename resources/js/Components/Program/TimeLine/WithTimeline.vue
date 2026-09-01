<script setup lang="ts">
import TimelinePoint from "./TimelinePoint.vue";

interface Props {
    startTime?: string;
    endTime?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="relative mx-auto w-full max-w-4xl py-4">
        <TimelinePoint
            v-if="startTime"
            :timestamp="startTime"
            label="Início"
            type="start"
        />

        <div
            class="relative my-6 space-y-6 sm:border-l sm:border-white/10 sm:pl-8"
        >
            <slot />
        </div>

        <TimelinePoint
            v-if="endTime"
            :timestamp="endTime"
            label="Fim"
            type="end"
        />
    </div>
</template>
