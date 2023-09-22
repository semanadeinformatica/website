<script setup lang="ts">
import type Event from "@/Types/Event";
import { Link } from "@inertiajs/vue3";

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
    <article
        class="relative flex flex-col border-b-2 border-2023-teal-dark pb-4"
    >
        <h2 class="text-2xl font-bold text-2023-orange">
            <em
                ><Link href="#" preserve-state preserve-scroll>{{
                    event.name
                }}</Link></em
            >
        </h2>
        <p class="text-lg text-2023-teal-dark">{{ event.topic }}</p>
        <ul v-if="event.users" class="flex flex-col">
            <li
                v-for="speaker in event.users"
                :key="speaker.id"
                class="font-bold text-2023-teal"
            >
                {{ speaker.name
                }}<span
                    v-if="
                        speaker.usertype_type === 'App\\Models\\Speaker' &&
                        speaker.usertype?.organization
                    "
                >
                    | {{ speaker.usertype?.organization }}</span
                >
            </li>
        </ul>
        <span v-if="event.capacity" class="text-2023-teal"
            >Capacidade:
            <span class="font-bold">{{ event.capacity }}</span></span
        >
        <span class="text-2023-teal">
            {{ formatTimeString(event.time_start) }} -
            {{ formatTimeString(event.time_end) }}
        </span>
        <span
            class="absolute -left-[calc(2rem+17.75px)] top-0 inline-flex h-8 w-8 items-center justify-center rounded-sm bg-2023-orange text-xl font-semibold text-white"
            >i</span
        >
    </article>
</template>
