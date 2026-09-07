<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProgramDaySelector from "@/Components/Program/ProgramDaySelector.vue";
import ProgramDayPanel from "@/Components/Program/ProgramDayPanel.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
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
        <div
            class="relative mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8"
        >
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
                <PillSelector
                    :items="[
                        { id: 'soon', label: 'Em breve...', disabled: true },
                    ]"
                    size="sm"
                    :wrap="false"
                    container-class="mb-4"
                />
                <p class="max-w-md text-sm text-neutral-400">
                    O programa da edição 2026 estará disponível brevemente.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
