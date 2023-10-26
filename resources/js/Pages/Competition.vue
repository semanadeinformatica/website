<script setup lang="ts">
import Podium from "@/Components/Podium.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import type Competition from "@/Types/Competition";
import { router } from "@inertiajs/vue3";
// import { computed } from "vue";
import route from "ziggy-js";
import { isAdmin } from "@/Types/User";

interface Props {
    competition: Competition;
    isParticipant: boolean;
    isEnrolled: boolean;
}

const { competition } = defineProps<Props>();

const formattedDate = (
    startDate: string,
    separator: string,
    endDate: string,
) => {
    const startDateArray = startDate.split(" ");
    const endDateArray = endDate.split(" ");

    let pointer = 0;

    while (startDateArray[pointer] === endDateArray[pointer]) pointer++;

    endDate = endDateArray.slice(pointer).join(" ");

    return `${startDate} ${separator} ${endDate}`;
};
</script>

<template>
    <AppLayout title="Competição">
        <header class="flex flex-col items-center gap-4 p-4 text-center">
            <h2
                class="w-fit border border-solid border-black bg-2023-red p-3 text-center text-2xl font-bold text-white shadow-md shadow-2023-teal xl:text-3xl 2xl:text-4xl"
            >
                {{ competition.name }}
            </h2>
            <span
                class="inline-flex w-3/4 justify-center text-xl font-bold text-2023-red-dark"
                >{{ competition.theme }}</span
            >
            <span
                class="inline-flex justify-center text-xl font-bold text-2023-teal"
                >{{
                    formattedDate(
                        $d(new Date(competition.date_start), "fullTime"),
                        "-",
                        $d(new Date(competition.date_end), "fullTime"),
                    )
                }}
            </span>
        </header>

        <Podium></Podium>
        <!-- RULES -->

        <section
            class="relative mt-5 border-t border-black bg-2023-teal-dark text-justify"
        >
            <h2
                class="absolute -top-9 left-1/2 -translate-x-1/2 transform border border-solid border-black bg-2023-red p-3 text-2xl font-bold text-white shadow-md shadow-2023-bg xl:text-3xl 2xl:text-4xl"
            >
                Regulamento
            </h2>
            <div
                class="prose prose-lg max-w-none break-words p-20 text-white max-lg:pb-10 lg:columns-2"
                v-html="competition.regulation_html"
            ></div>
        </section>

        <template v-if="!isAdmin($page.props.auth.user)">
            <!-- PARTICIPATE -->
            <section
                v-if="!isEnrolled"
                class="relative flex flex-col content-center items-center justify-center py-24"
            >
                <span class="text-2xl font-bold text-2023-teal">
                    Ainda não estás inscrito na SINF
                </span>
                <div
                    class="mx-10 flex h-full flex-1 flex-col items-center bg-2023-bg p-3 text-center text-3xl font-bold text-white sm:p-12"
                >
                    <PrimaryButton
                        color="orange"
                        shadow="teal"
                        text-size="sm:text-4xl"
                        padding="sm:px-8"
                        @click="
                            $page.props.auth.user
                                ? router.put(route('enroll'))
                                : router.get(route('register'))
                        "
                        >Inscreve-te</PrimaryButton
                    >
                </div>
            </section>
            <section
                v-else-if="isEnrolled && isParticipant"
                class="relative flex content-center items-center justify-center py-24"
            >
                <h1
                    class="absolute top-[1rem] m-4 flex justify-center p-3 text-4xl font-black text-2023-red"
                >
                    Vamos a isto?
                </h1>

                <a
                    :href="competition.registration_link"
                    class="relative mt-5 content-center justify-center border border-black bg-2023-teal px-8 py-2 text-center text-2xl font-semibold text-white shadow-2023-orange transition-shadow hover:shadow-md active:shadow-none"
                >
                    Participar!
                </a>
            </section>
        </template>
    </AppLayout>
</template>
