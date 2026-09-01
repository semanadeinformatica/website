<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type EventDay from "@/Types/EventDay";

interface Props {
    days?: EventDay[];
    totalDays: number;
    queryDay: number;
    eventDay?: EventDay;
}

defineProps<Props>();

const formatShortDate = (dateStr?: Date | string): string => {
    if (!dateStr) return "";
    try {
        const d = new Date(dateStr);
        return d
            .toLocaleDateString("pt-PT", {
                day: "numeric",
                month: "short",
                timeZone: "Europe/Lisbon",
            })
            .replace(".", "");
    } catch {
        return "";
    }
};
</script>

<template>
    <section class="mb-5 flex flex-col items-center gap-4 text-center">
        <div v-if="totalDays > 1" class="flex justify-center">
            <div class="pill-container flex-wrap justify-center gap-1.5 p-1.5">
                <Link
                    v-for="dayIndex in totalDays"
                    :key="dayIndex"
                    :href="
                        route(route().current() ?? 'program', {
                            day: dayIndex,
                        })
                    "
                    class="pill-item cursor-pointer gap-2 px-4 py-2 text-sm font-medium transition-all duration-200"
                    :class="{
                        'pill-item-active': dayIndex === queryDay,
                    }"
                    :only="['eventDay', 'queryDay']"
                    preserve-state
                    preserve-scroll
                >
                    <span class="font-semibold">Dia {{ dayIndex }}</span>
                    <template
                        v-if="
                            dayIndex === queryDay && days && days[dayIndex - 1]
                        "
                    >
                        <span class="opacity-35">•</span>
                        <span class="text-xs opacity-85">
                            {{ formatShortDate(days[dayIndex - 1]?.date) }}
                        </span>
                    </template>
                </Link>
            </div>
        </div>

        <div v-if="eventDay" class="flex flex-col items-center gap-1.5">
            <p
                v-if="eventDay.theme"
                class="max-w-2xl text-sm leading-relaxed font-normal sm:text-base"
            >
                {{ eventDay.theme }}
            </p>
        </div>
    </section>
</template>
