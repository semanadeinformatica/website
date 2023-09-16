<script setup lang="ts">
import type Event from "@/Types/Event";

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

const formatTimeString = (time: string): string => {
    const [hours, minutes] = time.split(":");

    return `${hours}h${minutes}`;
};
</script>

<template>
    <article class="relative border-b-2 border-2023-teal-dark pb-4">
        <h2 class="text-2xl font-bold text-2023-orange">
            <em
                ><a href="#">{{ event.topic }}</a></em
            >
        </h2>
        <p class="text-2023-teal">
            {{ formatTimeString(event.time_start) }} -
            {{ formatTimeString(event.time_end) }}
        </p>
        <span
            class="absolute -left-[calc(2rem+17.75px)] top-0 inline-flex h-8 w-8 items-center justify-center rounded-sm bg-2023-orange font-semibold text-white"
            >i</span
        >
    </article>
</template>
