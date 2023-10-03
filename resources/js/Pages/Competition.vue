<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import Podium from "@/Components/Podium.vue";
import type Competition from "@/Types/Competition";
import { computed } from "vue";

interface Props {
    competition: Competition;
}

const { competition } = defineProps<Props>();

const regulationTextParts = computed(() => {
    // See if this gets fucky - Nuno Pereira
    const parts = competition.regulation.split(" ");

    return [
        parts.slice(0, Math.ceil(parts.length / 2)).join(" "),
        parts.slice(Math.ceil(parts.length / 2)).join(" "),
    ];
});
</script>

<template>
    <AppLayout title="Competition">
        <header
            class="relative left-1/2 top-9 flex w-fit -translate-x-1/2 transform flex-col gap-4"
        >
            <h2
                class="border border-solid border-black bg-2023-red p-3 text-center text-2xl font-bold text-white shadow-md shadow-2023-teal xl:text-3xl 2xl:text-4xl"
            >
                {{ competition.name }}
            </h2>
            <span
                class="inline-flex w-full justify-center text-xl font-bold text-2023-teal"
                >{{ $d(new Date(competition.date_start), "short") }} -
                {{ $d(new Date(competition.date_end), "short") }}</span
            >
        </header>

        <Podium></Podium>
        <!-- RULES -->

        <section
            class="relative mt-5 grid grid-flow-row grid-cols-2 border-t border-black bg-2023-teal-dark text-justify max-lg:grid-flow-col max-lg:grid-cols-1 max-lg:grid-rows-2"
        >
            <h2
                class="absolute -top-9 left-1/2 -translate-x-1/2 transform border border-solid border-black bg-2023-red p-3 text-2xl font-bold text-white shadow-md shadow-2023-bg xl:text-3xl 2xl:text-4xl"
            >
                Regulamento
            </h2>

            <p class="p-20 text-lg text-white max-lg:pb-10">
                {{ regulationTextParts[0] }}
            </p>
            <p class="p-20 text-lg text-white max-lg:pb-10">
                {{ regulationTextParts[1] }}
            </p>
        </section>

        <!-- PARTICIPATE -->
        <section
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
    </AppLayout>
</template>
