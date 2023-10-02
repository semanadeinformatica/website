<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProgramDayPanel from "@/Components/Program/ProgramDayPanel.vue";
import type EventDay from "@/Types/EventDay";
import route from "ziggy-js";
import { Link } from "@inertiajs/vue3";

interface Props {
    eventDay: EventDay;
    queryDay: number;
    totalDays: number;
}

const { eventDay, queryDay, totalDays } = defineProps<Props>();
</script>

<template>
    <AppLayout title="Programa">
        <div
            v-if="totalDays !== 0"
            class="flex flex-col items-center px-10 py-10 md:px-40"
        >
            <h1
                class="mb-10 w-fit border border-black bg-2023-red p-2 px-5 text-2xl font-bold text-white shadow-md shadow-2023-bg"
            >
                Programa
            </h1>
            <section class="mb-5 flex flex-col items-center gap-5">
                <div
                    id="daySelection"
                    class="flex w-fit flex-row flex-wrap justify-center gap-4"
                >
                    <template v-for="(day, idx) in totalDays" :key="idx">
                        <Link
                            :href="
                                route(route().current() ?? 'program', {
                                    day,
                                })
                            "
                            as="span"
                            :only="['eventDay', 'queryDay']"
                            class="inline-flex h-16 w-16 cursor-pointer items-center justify-center rounded-sm bg-2023-teal text-xl font-bold text-white transition"
                            :class="{
                                selected:
                                    day ==
                                    queryDay /* cringe Inertia gives us a string, no strict equality boo-hoo */,
                            }"
                            preserve-state
                            preserve-scroll
                        >
                            {{ day }}
                        </Link>
                    </template>
                </div>
                <span class="font-bold text-2023-orange">{{
                    new Intl.DateTimeFormat("pt-PT", {
                        month: "long",
                        day: "2-digit",
                    }).format(new Date(eventDay.date))
                }}</span>
            </section>
            <ProgramDayPanel :key="eventDay.id" :day="eventDay" />
        </div>
        <div v-else class="flex items-center justify-center">
            <p class="pt-80 text-center text-5xl font-bold text-2023-teal-dark">
                Em breve...
            </p>
        </div>
    </AppLayout>
</template>

<style scoped>
.selected {
    background-color: rgb(242, 147, 37);
}
</style>
