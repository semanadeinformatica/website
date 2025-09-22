<script setup lang="ts">
import TimelinePoint from "./TimelinePoint.vue";

type Times = {
    startTime?: string;
    endTime?: string;
};

defineProps<Times>();
</script>

<template>
    <section
        class="mt-10 h-full w-full border-l-4 border-white p-8 py-10 lg:w-4/6"
    >
        <TimelinePoint :timestamp="startTime" label="Início" />
        <slot />
        <TimelinePoint :timestamp="endTime" label="Fim" />
    </section>
</template>
