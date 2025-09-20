<script setup lang="ts">
import type Event from "@/Types/Event";
import AppLayout from "@/Layouts/AppLayout.vue";
import SpeakerInfo from "@/Components/Event/SpeakerInfo.vue";
import { computed } from "vue";
import { isSpeaker, isCompany, isAdmin } from "@/Types/User";
import Sponsor from "@/Components/Home/Sponsor.vue";
import route from "ziggy-js";
import { router, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import type Enrollment from "@/Types/Enrollment";

interface Props {
    event: Event;
    enrollments?: Enrollment[];
    enrollmentCount: number;
    canJoin: boolean;
    isParticipant: boolean;
    hasJoined: boolean;
    isEnrolled: boolean;
    isStaff: boolean;
}

const props = defineProps<Props>();

const event = computed(() => props.event);

const speakers = computed(() => event.value.users?.filter(isSpeaker) ?? []);

// It's for you bb 😘 @toni-santos
// ty luv 😘 @ttoino
const companies = computed(() => event.value.users?.filter(isCompany) ?? []);

// FIXME: duplicated :P
const formatTimeString = (time: string): string => {
    return `1970-01-01T${time}.000000Z`;
};

const colorPicker = () => {
    const colors = ["white"];
    const pos = Math.floor(Math.random() * colors.length);
    
    return colors[pos];
};
</script>

<template>
    <AppLayout title="Evento">
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
            class="m relative mt-32 flex flex-row flex-wrap justify-center gap-8 bg-2025-blue px-16 py-24"
            :class="[
                { 'mt-20': companies.length > 0 && speakers.length === 0 },
            ]"
        >
            <h1
                class="absolute -top-7 mr-2 flex border border-black bg-2025-blue p-2 px-3 text-center text-2xl font-bold text-white shadow-md shadow-white max-lg:left-auto"
            >
                {{ event.name }}
            </h1>
            <div
                class="prose max-w-3xl break-words text-justify font-bold text-white"
                v-html="event.description_html"
            ></div>
            <h1
                class="absolute -bottom-5 mr-2 flex border border-black bg-2025-blue p-2 px-3 text-xl font-bold text-white shadow-md shadow-white max-lg:left-auto"
            >
                Dia
                {{ event.event_day ? $d(event.event_day.date, "day") : "N/A" }},
                {{
                    $d(
                        new Date(formatTimeString(event.time_start)),
                        "hourMinute",
                    )
                }}
                -
                {{
                    $d(new Date(formatTimeString(event.time_end)), "hourMinute")
                }}
                <template v-if="event.location">
                    @ {{ event.location }}
                </template>
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
                class="w-[200px] border border-black shadow shadow-white"
            >
                <Sponsor :company="company" />
            </div>
        </section>

        <div class="mt-16 flex w-full justify-center">
            <Link
                v-if="isAdmin($page.props.auth.user) || isStaff"
                :href="
                    route('user.scan-code', {
                        _query: {
                            event: event.id,
                        },
                    })
                "
                class="inline-flex border border-black bg-2025-blue p-2 px-3 text-center text-2xl font-bold text-white shadow-2023-teal transition hover:shadow-md"
            >
                Scan QR Code
            </Link>
        </div>

        <!-- sign up -->
        <div
            v-if="isParticipant"
            class="flex w-full flex-col items-center gap-4 place-self-center py-24"
        >
            <p
                class="flex w-fit flex-col text-center text-3xl font-bold text-text-color"
            >
                <span v-if="hasJoined">Vemo-nos lá!</span>
                <span v-else-if="!isEnrolled"
                    >Ainda não te inscreveste na SINF!</span
                >
                <template v-else-if="canJoin">
                    <span>Vamos a isto?</span>
                    <span v-if="event.capacity" class="text-lg">
                        Ainda temos
                        {{ event.capacity - enrollmentCount }}
                        lugares.
                    </span>
                </template>
                <span v-else>Evento esgotado!</span>
            </p>

            <PrimaryButton
                v-if="!isEnrolled || canJoin"
                color="teal-dark"
                shadow="red"
                text-size="sm:text-3xl"
                padding="sm:px-8"
                @click="
                    if (!(isEnrolled && event.external_url)) {
                        $page.props.auth.user
                            ? isEnrolled
                                ? router.put(route('event.join', event))
                                : router.get(route('home') + '#enroll-section') // HACK: this is a hack
                            : router.get(route('register'));
                    }
                "
            >
                <template v-if="!isEnrolled"
                    >Inscreve-te nesta edição!</template
                >
                <a
                    v-else-if="event.external_url"
                    :href="event.external_url"
                    target="_blank"
                    class="flex flex-col"
                >
                    Link para inscrição
                </a>
                <template v-else>Inscreve-te!</template>
            </PrimaryButton>

            <PrimaryButton
                v-else-if="isEnrolled && hasJoined"
                color="teal-dark"
                shadow="red"
                text-size="sm:text-3xl"
                padding="sm:px-8"
                @click="router.put(route('event.leave', event))"
            >
                Cancela a inscrição
            </PrimaryButton>
        </div>

        <div
            v-if="enrollments != null"
            class="flex flex-col items-stretch justify-center p-12"
        >
            <div
                v-if="enrollments.length > 0"
                class="flex flex-col items-center overflow-y-auto border border-black bg-2025-blue shadow-lg shadow-white"
            >
                <div
                    v-for="enrollment in enrollments"
                    :key="enrollment.id"
                    class="flex w-full items-center justify-between gap-3 p-3 even:bg-2025-bg even:bg-opacity-20"
                >
                    {{ enrollment.participant?.user?.name ?? enrollment.id }} -
                    {{ enrollment.participant?.user?.email ?? enrollment.id }}
                </div>
            </div>
            <p v-else class="text-center text-2xl font-bold text-text-color">
                Ainda nenhum participante se inscreveu neste evento.
            </p>
        </div>
    </AppLayout>
</template>
