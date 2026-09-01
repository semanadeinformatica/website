<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProgramDaySelector from "@/Components/Program/ProgramDaySelector.vue";
import ProgramDayPanel from "@/Components/Program/ProgramDayPanel.vue";
import type EventDay from "@/Types/EventDay";

interface Props {
    days?: EventDay[];
    eventDay?: EventDay;
    queryDay: number;
    totalDays: number;
}

defineProps<Props>();
</script>

<template>
    <AppLayout title="Programa">
        <div class="relative mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <template v-if="totalDays !== 0 && eventDay !== undefined">
                <ProgramDaySelector
                    :days="days"
                    :total-days="totalDays"
                    :query-day="queryDay"
                    :event-day="eventDay"
                />

                <ProgramDayPanel
                    :key="eventDay.id"
                    :day="eventDay"
                    :query-day="queryDay"
                />
            </template>

            <div
                v-else
                class="flex flex-col items-center justify-center py-28 text-center"
            >
                <div class="pill-container mb-4 px-6 py-2.5">
                    <span class="text-sm font-medium text-neutral-400">
                        Em breve...
                    </span>
                </div>
                <p class="max-w-md text-sm text-neutral-400">
                    O programa da edição 2026 estará disponível brevemente.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
