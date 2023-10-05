<script setup lang="ts">
import type EventDay from "@/Types/EventDay";
import { ref, onMounted, watch, type UnwrapRef, computed } from "vue";
import WithTimeline from "@/Components/Program/TimeLine/WithTimeline.vue";
import StandDisplay from "@/Components/Program/TimeLine/StandDisplay.vue";
import ActivityTimelineAction from "./TimeLine/ActivityTimelineItem.vue";
import TalkTimelineAction from "./TimeLine/TalkTimelineItem.vue";

interface Props {
    day: EventDay;
}

const { day } = defineProps<Props>();

const selected = ref<HTMLElement | null>(null);
const selectedType = ref<"talk" | "activity" | "stand">("talk");

const noInfo = computed(
    () =>
        day.workshops?.length == 0 &&
        day.talks?.length == 0 &&
        day.talks?.length == 0,
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
                {{ $t("events.talks") }}
            </button>
            <button
                v-if="(day.workshops?.length ?? 0) > 0"
                class="transition"
                data-type="activity"
                @click="toggle"
            >
                {{ $t("pages.event.workshops") }}
            </button>
            <button
                v-if="(day.stands?.length ?? 0) > 0"
                class="transition"
                data-type="stand"
                @click="toggle"
            >
                {{ $t("pages.event.stands") }}
            </button>
        </div>
    </section>
    <p
        v-if="selectedType !== 'stand' && !noInfo"
        class="mr-2 mt-5 max-w-2xl border border-solid border-black p-2.5 px-8 text-justify text-lg font-bold text-2023-teal shadow-md shadow-2023-teal"
    >
        {{ day.theme }}
    </p>
    <template v-if="noInfo">
        <p class="pt-40 text-4xl font-bold text-2023-teal-dark">
            {{ $t("general.soon") }}
        </p>
    </template>
    <template v-else>
        <WithTimeline>
            <div class="flex flex-col gap-8">
                <template v-if="selectedType === 'stand'">
                    <StandDisplay :stands="day.stands!" />
                </template>

                <template v-else-if="selectedType === 'activity'">
                    <ActivityTimelineAction
                        v-for="workshop in day.workshops"
                        :key="workshop.id"
                        :event="workshop"
                    />
                </template>

                <template v-else-if="selectedType === 'talk'">
                    <TalkTimelineAction
                        v-for="talk in day.talks"
                        :key="talk.id"
                        :event="talk"
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
