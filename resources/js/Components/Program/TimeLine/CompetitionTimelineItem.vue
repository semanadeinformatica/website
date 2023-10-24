<script setup lang="ts">
import type Competition from "@/Types/Competition";
import route from "ziggy-js";
import { Link } from "@inertiajs/vue3";

interface Props {
    competition: Competition;
}

defineProps<Props>();
</script>

<template>
    <article
        class="relative flex flex-col border-b-2 border-2023-teal-dark pb-4"
    >
        <h2 class="text-2xl font-bold text-2023-orange">
            <em
                ><Link
                    :href="route('competition.show', competition)"
                    preserve-state
                    preserve-scroll
                    >{{ competition.name }}</Link
                ></em
            >
        </h2>
        <p class="text-lg text-2023-teal-dark">{{ competition.theme }}</p>
        <span class="text-2023-teal">
            {{ $d(new Date(competition.date_start), "long") }} -
            {{ $d(new Date(competition.date_end), "long") }}
        </span>
        <span
            class="absolute -left-[calc(2rem+17.75px)] top-0 inline-flex h-8 w-8 items-center justify-center rounded-sm bg-2023-orange text-xl font-semibold text-white"
            >i</span
        >
    </article>
</template>
