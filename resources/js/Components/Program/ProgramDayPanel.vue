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

const { day } = defineProps<Props>();

const selected = ref<HTMLElement | null>(null);
const selectedType = ref<"talk" | "activity" | "stand" | "competitions">(
    "talk",
);

const noInfo = computed(
    () =>
        day.activities?.length === 0 &&
        day.talks?.length === 0 &&
        day.stands?.length === 0 &&
        day.competitions?.length === 0,
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
            const [hour, minutes] = time.split(":");

            return `${hour}h${minutes}`;
        };

        const parseDate = (dateStr?: string) => {
            if (!dateStr) return undefined;

            const date = new Date(dateStr);

            console.log(dateStr, date);

            // FIXME: xD
            return `${
                date.getUTCHours() < 10
                    ? `0${date.getUTCHours()}`
                    : date.getUTCHours()
            }h${
                date.getMinutes() < 10
                    ? `0${date.getMinutes()}`
                    : date.getMinutes()
            }`;
        };

        let items: Event[] = [];
        switch (_selectedType) {
            case "activity":
                items = day.activities ?? [];
                break;
            case "talk":
                items = day.talks ?? [];
                break;
            case "stand":
                times.value = { start: "10h00", end: "17h00" };
                return;
            case "competitions":
                if (day.competitions?.length === 0)
                    times.value = {
                        start: undefined,
                        end: undefined,
                    };
                else
                    times.value = {
                        start: parseDate(day.competitions?.[0].date_start),
                        end: parseDate(
                            day.competitions?.[day.competitions.length - 1]
                                .date_end,
                        ),
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
            class="flex flex-row justify-center gap-4 font-bold text-2023-teal"
        >
            <button
                v-if="(day.talks?.length ?? 0) > 0"
                class="transition"
                data-type="talk"
                @click="toggle"
            >
                Palestras
            </button>
            <button
                v-if="(day.activities?.length ?? 0) > 0"
                class="transition"
                data-type="activity"
                @click="toggle"
            >
                Atividades
            </button>
            <button
                v-if="(day.stands?.length ?? 0) > 0"
                class="transition"
                data-type="stand"
                @click="toggle"
            >
                Bancas
            </button>
            <button
                v-if="(day.competitions?.length ?? 0) > 0"
                class="transition"
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
        class="mr-2 mt-5 max-w-2xl border border-solid border-black p-2.5 px-8 text-justify text-lg font-bold text-2023-teal shadow-md shadow-2023-teal"
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
    color: rgb(242, 147, 37);
    text-decoration: underline;
}
</style>
