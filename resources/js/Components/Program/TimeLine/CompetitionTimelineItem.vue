<script setup lang="ts">
import type Competition from "@/Types/Competition";
import { route } from "ziggy-js";
import { Link } from "@inertiajs/vue3";

interface Props {
    competition: Competition;
}

defineProps<Props>();

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
    <article class="relative flex flex-col border-b-2 border-white pb-4">
        <h2 class="text-text-color text-2xl font-bold">
            <em
                ><Link
                    :href="
                        route('competition.show', {
                            competition: competition.slug,
                        })
                    "
                    preserve-state
                    preserve-scroll
                    >{{ competition.name }}</Link
                ></em
            >
        </h2>
        <p class="text-text-color text-lg">{{ competition.theme }}</p>
        <span class="text-text-color">
            {{
                formattedDate(
                    $d(new Date(competition.date_start), "fullTime"),
                    "-",
                    $d(new Date(competition.date_end), "fullTime"),
                )
            }}
        </span>
        <span
            class="bg-2025-blue absolute top-0 -left-[calc(2rem+17.75px)] inline-flex h-8 w-8 items-center justify-center rounded-md text-xl font-semibold text-white"
            >i</span
        >
    </article>
</template>
