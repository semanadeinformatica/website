<script setup lang="ts">
import type Event from "@/Types/Event";
import AppLayout from "@/Layouts/AppLayout.vue";
import SpeakerInfo from "@/Components/Event/SpeakerInfo.vue";
import { computed } from "vue";
import type { CompanyUser, SpeakerUser } from "@/Types/User";
import Sponsor from "@/Components/Home/Sponsor.vue";
import route from "ziggy-js";
import { router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface Props {
    event: Event;
    canJoin: boolean;
    hasJoined: boolean;
    isEnrolled: boolean;
}

const { event } = defineProps<Props>();

const speakers = computed(
    () =>
        event.users?.filter(
            (u): u is SpeakerUser => u.usertype_type === "App\\Models\\Speaker",
        ) ?? [],
);

// It's for you bb 😘 @toni-santos
// ty luv 😘 @ttoino
const companies = computed(
    () =>
        event.users?.filter(
            (u): u is CompanyUser => u.usertype_type === "App\\Models\\Company",
        ) ?? [],
);

// FIXME: duplicated :P
const formatTimeString = (time: string): string => {
    const [hours, minutes] = time.split(":");

    return `${hours}h${minutes}`;
};

const colorPicker = () => {
    const pos = Math.floor(Math.random() * 5);
    return ["orange", "teal-dark", "red-dark", "red", "teal"][pos];
};
</script>

<template>
    <AppLayout :title="$t('pages.event.title')">
        <!-- speaker/event intro -->
        <section class="mx-9 flex flex-col gap-6 pt-10">
            <SpeakerInfo
                v-for="(speaker, idx) in speakers"
                :key="speaker.id"
                :user="speaker"
                :reverse="idx % 2"
                :color="colorPicker()"
            ></SpeakerInfo>
        </section>
        <!-- details -->
        <section
            class="relative mt-32 flex flex-row flex-wrap justify-center gap-8 bg-2023-teal-dark px-16 py-24"
            :class="[
                { 'mt-20': companies.length > 0 && speakers.length === 0 },
            ]"
        >
            <h1
                class="absolute -top-7 mr-2 flex border border-black bg-2023-red p-2 px-3 text-center text-2xl font-bold text-white shadow-md shadow-2023-bg max-lg:left-auto"
            >
                {{ event.name }}
            </h1>
            <div class="max-w-3xl text-justify font-bold text-white">
                {{ event.description }}
            </div>
            <h1
                class="absolute -bottom-5 mr-2 flex border border-black bg-2023-red-dark p-2 px-3 text-xl font-bold text-white shadow-md shadow-2023-bg max-lg:left-auto"
            >
                <template v-if="event.event_day?.date">
                    Dia
                    {{ $d(event.event_day.date, "day") }}
                    @
                    {{
                        event.event_day
                            ? formatTimeString(event.time_start)
                            : ""
                    }}
                    <template v-if="event.room"> - {{ event.room }} </template>
                </template>
                <template v-else> N/A </template>
            </h1>
        </section>
        <!-- companies -->
        <section
            v-if="companies.length > 0"
            class="mx-9 mt-24 flex flex-row flex-wrap items-center justify-center gap-6"
        >
            <div
                v-for="(company, idx) in companies"
                :key="idx"
                class="w-[200px] border border-black shadow shadow-2023-red"
            >
                <Sponsor :company="company" />
            </div>
        </section>
        <!-- sign up -->
        <div
            class="flex w-full flex-col items-center gap-4 place-self-center py-24"
        >
            <p
                class="flex w-fit flex-col gap-2 text-center text-3xl font-bold text-2023-red"
            >
                <span v-if="hasJoined">{{ $t("pages.event.seeYou") }}</span>
                <span v-else-if="!isEnrolled">{{
                    $t("pages.event.notEnrolled")
                }}</span>
                <template v-else-if="canJoin">
                    <span>{{ $t("pages.event.callToAction") }}</span>
                    <span v-if="event.capacity" class="text-lg">
                        {{ $t("pages.event.spots.pre") }}
                        {{ event.capacity - (event.enrollments?.length ?? 0) }}
                        {{ $t("pages.event.spots.pos", event.capacity) }}
                    </span>
                </template>
                <span v-else>{{ $t("pages.event.full") }}</span>
            </p>

            <PrimaryButton
                v-if="!isEnrolled || canJoin"
                color="teal-dark"
                shadow="red"
                text-size="sm:text-3xl"
                padding="sm:px-8"
                @click="
                    $page.props.auth.user
                        ? isEnrolled
                            ? router.put(route('event.join', event))
                            : router.get(route('home') + '#enroll-section') // HACK: this is a hack
                        : router.get(route('register'))
                "
            >
                <span v-if="!isEnrolled" class="flex flex-col"
                    >Inscreve-te nesta edição!</span
                >
                <span v-else class="flex flex-col"> Inscreve-te! </span>
            </PrimaryButton>
        </div>
    </AppLayout>
</template>
