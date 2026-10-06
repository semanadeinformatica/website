<script setup lang="ts">
import { computed, ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ProgramSidebar, {
    type TabOption,
} from "@/Components/Program/ProgramSidebar.vue";
import ProgramTimeline from "@/Components/Program/ProgramTimeline.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
import QuickScroll from "@/Components/UI/QuickScroll.vue";
import type EventDay from "@/Types/EventDay";

interface Props {
    days?: EventDay[];
    eventDay?: EventDay;
    queryDay: number;
    totalDays: number;
}

const props = defineProps<Props>();

const availableTabs = computed<TabOption[]>(() => {
    if (!props.eventDay) return [];
    const tabs: TabOption[] = [];
    const talksCount = props.eventDay.talks?.length ?? 0;
    const activitiesCount = props.eventDay.activities?.length ?? 0;
    const competitionsCount = props.eventDay.competitions?.length ?? 0;
    const standsCount = props.eventDay.stands?.length ?? 0;

    const timelineTotal = talksCount + activitiesCount + competitionsCount;

    if (timelineTotal > 0) {
        tabs.push({
            id: "all",
            label: "Todos",
            count: timelineTotal,
        });
    }

    if (talksCount > 0) {
        tabs.push({
            id: "talk",
            label: "Palestras",
            count: talksCount,
        });
    }

    if (activitiesCount > 0) {
        tabs.push({
            id: "activity",
            label: "Atividades",
            count: activitiesCount,
        });
    }

    if (standsCount > 0) {
        tabs.push({
            id: "stand",
            label: "Bancas",
            count: standsCount,
        });
    }

    if (competitionsCount > 0) {
        tabs.push({
            id: "competitions",
            label: "Competições",
            count: competitionsCount,
        });
    }

    return tabs;
});

const selectedType = ref<string>("all");

watch(
    availableTabs,
    (tabs) => {
        if (tabs.length > 0 && !tabs.some((t) => t.id === selectedType.value)) {
            selectedType.value = tabs[0]?.id ?? "all";
        }
    },
    { immediate: true },
);
</script>

<template>
    <AppLayout title="Programa">
        <QuickScroll mode="top" />

        <div
            class="relative mx-auto w-full max-w-7xl px-4 pt-0 pb-12 sm:px-6 sm:pb-16 lg:px-8 lg:pt-8"
        >
            <template v-if="totalDays !== 0 && eventDay !== undefined">
                <div class="lg:flex lg:items-start lg:gap-8 xl:gap-12">
                    <aside
                        class="w-full lg:sticky lg:top-24 lg:w-64 lg:shrink-0 xl:w-72"
                    >
                        <ProgramSidebar
                            :days="days ?? []"
                            :current-day="queryDay"
                            :total-days="totalDays"
                            :event-day="eventDay"
                            :selected-type="selectedType"
                            :available-tabs="availableTabs"
                            @select-type="selectedType = $event"
                        />
                    </aside>

                    <div class="min-w-0 flex-1">
                        <ProgramTimeline
                            :event-day="eventDay"
                            :selected-type="selectedType"
                        />
                    </div>
                </div>
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
