<script setup lang="ts">
import type Competition from "@/Types/Competition";
import { route } from "ziggy-js";
import { Link } from "@inertiajs/vue3";

interface Props {
    competition: Competition;
}

defineProps<Props>();

const formatDateTime = (dateStr?: string): string => {
    if (!dateStr) return "";
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString("pt-PT", {
            day: "numeric",
            month: "short",
            hour: "2-digit",
            minute: "2-digit",
            timeZone: "Europe/Lisbon",
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <div class="group relative">
        <div
            class="absolute top-8 -left-9.25 hidden h-2.5 w-2.5 rounded-full bg-neutral-600 ring-4 ring-black transition-all duration-300 group-hover:scale-125 sm:flex"
        />

        <article
            class="rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out hover:scale-[1.01] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-7"
        >
            <div class="mb-4 flex flex-wrap items-center gap-2.5">
                <div
                    class="pill-container gap-1.5 px-3 py-1 text-xs text-neutral-300 shadow-none"
                >
                    <svg
                        class="h-3.5 w-3.5 text-neutral-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <span>
                        {{ formatDateTime(competition.date_start) }}
                        <template v-if="competition.date_end">
                            - {{ formatDateTime(competition.date_end) }}
                        </template>
                    </span>
                </div>
            </div>

            <h3 class="text-xl font-bold tracking-tight sm:text-2xl">
                <Link
                    :href="
                        route('competition.show', {
                            competition: competition.slug,
                        })
                    "
                    class="inline-flex items-center gap-2 text-white transition-colors hover:text-neutral-200"
                    preserve-state
                    preserve-scroll
                >
                    <span>{{ competition.name }}</span>
                    <svg
                        class="h-4 w-4 text-neutral-500 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                        />
                    </svg>
                </Link>
            </h3>

            <p
                v-if="competition.theme"
                class="mt-2 text-sm leading-relaxed text-neutral-300 sm:text-base"
            >
                {{ competition.theme }}
            </p>
        </article>
    </div>
</template>
