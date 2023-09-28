<script setup lang="ts">
import type Event from "@/Types/Event";
import type { SpeakerUser } from "@/Types/User";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

const speakers = computed(
    () =>
        event.users
            ?.filter((user) => user.usertype_type === "App\\Models\\Speaker")
            .map((user) => user as SpeakerUser),
);

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
        <ul v-if="speakers" class="flex flex-col">
            <li
                v-for="user in speakers"
                :key="user.id"
                class="font-bold text-2023-teal"
            >
                {{ user.name
                }}<span v-if="user.usertype?.organization">
                    | {{ user.usertype.organization }}</span
                >
            </li>
        </ul>
        <span class="text-2023-teal"
            >Capacidade:
            <span class="font-bold">{{ event.capacity! }}</span></span
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
