<script setup lang="ts">
import type Event from "@/Types/Event";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SpeakerInfo from "@/Components/Event/SpeakerInfo.vue";
import { computed } from "vue";

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

const date_start = new Date(event.date_start);
const date_day = `${date_start.getDay()}/${date_start.getMonth()}`;
const date_time = `${date_start.getHours()}:${date_start.getMinutes()}`;
// const date_str = `${date_start.getDay()}/${date_start.getMonth()} @ ${date_start.getHours()}:${date_start.getMinutes()}`;

const speakers = computed(
    () =>
        event.speakers?.map((speaker) => ({
            id: speaker.id,
            name: speaker.name,
            title: speaker.title,
            description: speaker.description,
            organization: speaker.organization,
            social_media: speaker.social_media,
            event_id: speaker.event_id,
            created_at: speaker.created_at,
            updated_at: speaker.updated_at,
        })),
);
</script>

<template>
    <AppLayout title="Event">
        <!-- speaker/event intro -->
        <section class="flex flex-col">
            <SpeakerInfo
                v-for="(speaker, idx) in speakers"
                :key="speaker.id"
                :speaker="speaker"
                :reverse="idx % 2"
            ></SpeakerInfo>
        </section>
        <!-- details -->
        <section
            class="relative mt-32 flex flex-row flex-wrap justify-center gap-8 bg-2023-teal-dark px-16 py-24"
        >
            <h1
                class="absolute -top-7 left-40 border border-black bg-2023-red p-2 px-3 text-2xl font-bold text-white shadow-md shadow-2023-bg max-lg:left-auto"
            >
                {{ event.name }}
            </h1>
            <div class="max-w-3xl text-justify font-bold text-white">
                {{ event.description }}
            </div>
            <div
                class="absolute -bottom-10 right-32 flex flex-row items-center gap-5 max-lg:right-auto"
            >
                <div class="relative w-fit">
                    <img
                        class="w-16"
                        src="../../../public/images/mini-calendar.svg"
                        alt=""
                    />
                    <span
                        class="absolute left-1/2 top-1/2 w-fit -translate-x-1/2 -translate-y-1/2 pt-4 text-xl font-bold text-2023-teal-dark"
                        >{{ date_day }}</span
                    >
                </div>
                <div class="relative h-fit">
                    <img
                        class="w-20"
                        src="../../../public/images/alarm-clock.svg"
                        alt=""
                    />
                    <span
                        class="absolute left-1/2 top-1/2 w-fit -translate-x-1/2 -translate-y-1/2 pt-1 text-xl font-bold text-2023-red"
                        >{{ date_time }}</span
                    >
                </div>
            </div>
        </section>
        <!-- sign up -->
        <section
            class="flex w-full flex-col items-center gap-4 place-self-center py-24"
        >
            <p class="w-fit text-3xl font-bold text-2023-red">Vamos a isto?</p>
            <PrimaryButton class="w-fit text-xl font-bold"
                >Participar</PrimaryButton
            >
        </section>
    </AppLayout>
</template>

<style></style>
