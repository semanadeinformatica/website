<script setup lang="ts">
import type EventDay from "@/Types/EventDay";
import { ref, computed, watch } from "vue";
import WithTimeline from "@/Components/Program/TimeLine/WithTimeline.vue";
import StandDisplay from "@/Components/Program/TimeLine/StandDisplay.vue";
import ActivityTimelineItem from "./TimeLine/ActivityTimelineItem.vue";
import TalkTimelineItem from "./TimeLine/TalkTimelineItem.vue";
import CompetitionTimelineItem from "./TimeLine/CompetitionTimelineItem.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";

interface Props {
    day: EventDay;
}

const props = defineProps<Props>();
const day = computed(() => props.day);

type TabType = "talk" | "activity" | "stand" | "competitions";

interface TabOption {
    id: TabType;
    label: string;
    count: number;
}

const availableTabs = computed<TabOption[]>(() => {
    const tabs: TabOption[] = [];
    if (day.value.talks?.length) {
        tabs.push({
            id: "talk",
            label: "Palestras",
            count: day.value.talks.length,
        });
    }
    if (day.value.activities?.length) {
        tabs.push({
            id: "activity",
            label: "Atividades",
            count: day.value.activities.length,
        });
    }
    if (day.value.stands?.length) {
        tabs.push({
            id: "stand",
            label: "Bancas",
            count: day.value.stands.length,
        });
    }
    if (day.value.competitions?.length) {
        tabs.push({
            id: "competitions",
            label: "Competições",
            count: day.value.competitions.length,
        });
    }
    return tabs;
});

const selectedType = ref<TabType>(availableTabs.value[0]?.id ?? "talk");

// If day changes or current tab has no items, switch to first available tab
watch(
    availableTabs,
    (tabs) => {
        if (!tabs.some((t) => t.id === selectedType.value) && tabs.length > 0) {
            const firstTab = tabs[0];
            if (firstTab) {
                selectedType.value = firstTab.id;
            }
        }
    },
    { immediate: true },
);

const times = computed<{ start?: string; end?: string }>(() => {
    const parseTimeString = (time: string) => `1970-01-01T${time}.000000Z`;

    switch (selectedType.value) {
        case "activity": {
            const items = day.value.activities ?? [];
            if (!items.length) return {};
            const first = items[0];
            const last = items[items.length - 1];
            return {
                start: first ? parseTimeString(first.time_start) : undefined,
                end: last ? parseTimeString(last.time_end) : undefined,
            };
        }
        case "talk": {
            const items = day.value.talks ?? [];
            if (!items.length) return {};
            const first = items[0];
            const last = items[items.length - 1];
            return {
                start: first ? parseTimeString(first.time_start) : undefined,
                end: last ? parseTimeString(last.time_end) : undefined,
            };
        }
        case "stand":
            return {
                start: "1970-01-01T08:00:00.000000Z",
                end: "1970-01-01T17:00:00.000000Z",
            };
        case "competitions": {
            const comps = day.value.competitions ?? [];
            if (!comps.length) return {};
            const first = comps[0];
            const last = comps[comps.length - 1];
            return {
                start: first?.date_start,
                end: last?.date_end,
            };
        }
        default:
            return {};
    }
});
</script>

<template>
    <div class="w-full">
        <div v-if="availableTabs.length > 1" class="mb-10 flex justify-center">
            <PillSelector
                v-model="selectedType"
                :items="availableTabs"
                size="md"
            />
        </div>

        <div
            v-if="availableTabs.length === 0"
            class="flex flex-col items-center justify-center py-20 text-center"
        >
            <PillSelector
                :items="[{ id: 'none', label: 'Sem eventos agendados', disabled: true }]"
                size="sm"
                :wrap="false"
                container-class="mb-3"
            />
            <p class="text-xs text-neutral-500">
                Nenhuma atividade ou palestra disponível para este dia de
                momento.
            </p>
        </div>

        <template v-else-if="selectedType === 'stand'">
            <StandDisplay :stands="day.stands ?? []" />
        </template>

        <template v-else>
            <WithTimeline :start-time="times.start" :end-time="times.end">
                <template v-if="selectedType === 'activity'">
                    <ActivityTimelineItem
                        v-for="activity in day.activities"
                        :key="activity.id"
                        :event="activity"
                    />
                </template>

                <template v-else-if="selectedType === 'talk'">
                    <TalkTimelineItem
                        v-for="talk in day.talks"
                        :key="talk.id"
                        :event="talk"
                    />
                </template>

                <template v-else-if="selectedType === 'competitions'">
                    <CompetitionTimelineItem
                        v-for="competition in day.competitions"
                        :key="competition.id"
                        :competition="competition"
                    />
                </template>
            </WithTimeline>
        </template>
    </div>
</template>
