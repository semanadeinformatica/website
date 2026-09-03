<script setup lang="ts">
import type Competition from "@/Types/Competition";
import { route } from "ziggy-js";
import { Link } from "@inertiajs/vue3";
import Card from "@/Components/UI/Card.vue";
import { Calendar, ArrowRight } from "@lucide/vue";

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

        <Card as="article" padding="p-6 sm:p-7">
            <template #header>
                <div class="mb-4 flex flex-wrap items-center gap-2.5">
                    <div
                        class="pill-container gap-1.5 px-3 py-1 text-xs text-neutral-300 shadow-none"
                    >
                        <Calendar :size="14" class="text-neutral-400" />
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
                        <ArrowRight
                            :size="16"
                            class="text-neutral-500 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-white"
                        />
                    </Link>
                </h3>
            </template>

            <p
                v-if="competition.theme"
                class="mt-2 text-sm leading-relaxed text-neutral-300 sm:text-base"
            >
                {{ competition.theme }}
            </p>
        </Card>
    </div>
</template>
