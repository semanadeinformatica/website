<script setup lang="ts">
import type EventDay from "@/Types/EventDay";
import { ref, onMounted, watch, type UnwrapRef, computed } from "vue";
import WithTimeline from "@/Components/Program/TimeLine/WithTimeline.vue";
import StandDisplay from "@/Components/Program/TimeLine/StandDisplay.vue";
import ActivityTimelineAction from "./TimeLine/ActivityTimelineItem.vue";
import TalkTimelineAction from "./TimeLine/TalkTimelineItem.vue";
import CompetitionTimelineItem from "./TimeLine/CompetitionTimelineItem.vue";
import type Event from "@/Types/Event";

interface Props {
    day: EventDay;
}

const props = defineProps<Props>();

const day = computed(() => props.day);

const selected = ref<HTMLElement | null>(null);
const selectedType = ref<"talk" | "activity" | "stand" | "competitions">(
    "talk",
);

const noInfo = computed(
    () =>
        day.value.activities?.length === 0 &&
        day.value.talks?.length === 0 &&
        day.value.stands?.length === 0 &&
        day.value.competitions?.length === 0,
);

const toggle = ({ target }: MouseEvent) => {
    selected.value = target as HTMLElement;
};

watch(selected, (newValue, oldValue) => {
    oldValue?.classList.toggle("selected");
    newValue?.classList.toggle("selected");
    selectedType.value =
        (newValue?.dataset.type as UnwrapRef<typeof selectedType>) ?? "talk";
});

const times = ref<{ start?: string; end?: string }>({
    start: undefined,
    end: undefined,
});

watch(
    selectedType,
    (_selectedType) => {
        const parseTimeString = (time: string) => {
            return `1970-01-01T${time}.000000Z`;
        };

        let items: Event[] = [];
        switch (_selectedType) {
            case "activity":
                items = day.value.activities ?? [];
                break;
            case "talk":
                items = day.value.talks ?? [];
                break;
            case "stand":
                times.value = {
                    start: "1970-01-01T09:00:00.000000Z",
                    end: "1970-01-01T16:00:00.000000Z",
                };
                return;
            case "competitions":
                if (day.value.competitions?.length === 0)
                    times.value = {
                        start: undefined,
                        end: undefined,
                    };
                else {
                    times.value = {
                        start: day.value.competitions?.[0].date_start,
                        end: day.value.competitions?.[
                            day.value.competitions.length - 1
                        ].date_end,
                    };
                }
                return;
        }

        if (items.length === 0) {
            times.value = {
                start: undefined,
                end: undefined,
            };
            return;
        }

        const startTime = parseTimeString(items[0].time_start);
        const endTime = parseTimeString(items[items.length - 1].time_end);

        times.value = { start: startTime, end: endTime };
    },
    {
        immediate: true,
    },
);

onMounted(() => {
    selected.value = document.querySelector(
        "#tab-picker > button:first-of-type",
    ) as HTMLElement;
    if (selected.value) {
        selectedType.value =
            (selected.value.dataset.type as UnwrapRef<typeof selectedType>) ??
            "talk";
    }
});
</script>

<template>
    <section class="h-full w-full">
        <div
            id="tab-picker"
            class="flex flex-row justify-center gap-4 font-bold text-2025-blue-dark"
        >
            <button
                v-if="(day.talks?.length ?? 0) > 0"
                class=""
                data-type="talk"
                @click="toggle"
            >
                Palestras
            </button>
            <button
                v-if="(day.activities?.length ?? 0) > 0"
                class=""
                data-type="activity"
                @click="toggle"
            >
                Atividades
            </button>
            <button
                v-if="(day.stands?.length ?? 0) > 0"
                class=""
                data-type="stand"
                @click="toggle"
            >
                Bancas
            </button>
            <button
                v-if="(day.competitions?.length ?? 0) > 0"
                class=""
                data-type="competitions"
                @click="toggle"
            >
                Competições
            </button>
        </div>
    </section>
    <p
        v-if="
            (selectedType === 'talk' || selectedType === 'activity') && !noInfo
        "
        class="mr-2 mt-5 max-w-2xl border border-solid rounded-md border-white p-2.5 px-8 text-justify text-lg font-bold text-text-color shadow-md shadow-black/80"
    >
        {{ day.theme }}
    </p>
    <template v-if="noInfo">
        <p class="pt-40 text-4xl font-bold text-2023-teal-dark">Em breve...</p>
    </template>
    <template v-else>
        <WithTimeline :start-time="times.start" :end-time="times.end">
            <div class="flex flex-col gap-8">
                <template v-if="selectedType === 'stand'">
                    <StandDisplay :stands="day.stands!" />
                </template>

                <template v-else-if="selectedType === 'activity'">
                    <ActivityTimelineAction
                        v-for="activity in day.activities"
                        :key="activity.id"
                        :event="activity"
                    />
                </template>

                <template v-else-if="selectedType === 'talk'">
                    <TalkTimelineAction
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
            </div>
        </WithTimeline>
    </template>
</template>

<style scoped>
.selected {
    color: rgb(255, 255, 255);
    text-decoration: underline;
}
</style>
