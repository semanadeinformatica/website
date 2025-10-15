<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProgramDayPanel from "@/Components/Program/ProgramDayPanel.vue";
import type EventDay from "@/Types/EventDay";
import route from "ziggy-js";
import { Link } from "@inertiajs/vue3";

interface Props {
    eventDay?: EventDay;
    queryDay: number;
    totalDays: number;
}

defineProps<Props>();
</script>

<template>
    <AppLayout title="Programa">
        <div
            v-if="totalDays !== 0 && eventDay !== undefined"
            class="flex flex-col items-center px-10 py-10 md:px-40"
        >
            <h2
                class="mb-10 w-fit bg-2025-blue rounded-md p-2 px-5 text-3xl font-bold text-white "
            >
                Programa
            </h2>
            
            <section class="mb-5 flex flex-col items-center gap-5">
                <div
                    id="daySelection"
                    class="flex w-fit flex-row flex-wrap justify-center gap-4 "
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
                            class="inline-flex h-16 w-16 cursor-pointer items-center justify-center rounded-sm bg-2025-bg text-xl font-bold text-white transition rounded-xl transition-transform duration-300 hover:scale-105
                            [filter:drop-shadow(0_0_0_rgba(0,0,0,0))]
                            hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]
                            focus:outline-none"
                            :class="day == queryDay ? 'bg-2025-blue' : ''"
                            preserve-state
                            preserve-scroll
                        >
                            {{ day }}
                        </Link>
                    </template>
                </div>
                <span class="font-bold text-text-color">{{
                    $d(new Date(eventDay.date), "long")
                }}</span>
            </section>
            <ProgramDayPanel :key="eventDay.id" :day="eventDay" />
        </div>
        <div v-else class="flex items-center justify-center">
            <p class="pt-80 text-center text-5xl font-bold text-white">
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
