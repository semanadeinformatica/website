<script setup lang="ts">
import type EventDay from "@/Types/EventDay";
import { ref, onMounted, watch, type UnwrapRef, computed } from "vue";
import WithTimeline from "@/Components/Program/TimeLine/WithTimeline.vue";
import EventTimelineItem from "@/Components/Program/TimeLine/EventTimelineItem.vue";
import type Stand from "@/Types/Stand";
import type Event from "@/Types/Event";
import StandDisplay from "@/Components/Program/TimeLine/StandDisplay.vue";

interface Props {
    day: EventDay;
}

const { day } = defineProps<Props>();

const selected = ref<HTMLElement | null>(null);
const selectedType = ref<"talk" | "activity" | "stand">("talk");

const items = computed(() => {
    switch (selectedType.value) {
        case "talk":
            return day.events.filter((event) => event.capacity === null);
        case "activity":
            return day.events.filter(
                (event) => event.capacity && event.capacity > 0,
            );
        case "stand":
            return day.stands;
        default:
            return [];
    }
});
const stands = (items: Stand[] | Event[]): items is Stand[] => {
    return items.length > 0 && "sponsor_id" in items[0]; // HACK: dangerous - Nuno Pereira
};

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
    selectedType.value =
        (selected.value.dataset.type as UnwrapRef<typeof selectedType>) ??
        "talk";
});
</script>

<template>
    <section class="h-full w-full pt-8">
        <div
            id="tab-picker"
            class="flex flex-row justify-center gap-4 font-bold text-2023-teal"
        >
            <button class="transition" data-type="talk" @click="toggle">
                Palestras
            </button>
            <button class="transition" data-type="activity" @click="toggle">
                Atividades
            </button>
            <button class="transition" data-type="stand" @click="toggle">
                Bancas
            </button>
        </div>
    </section>
    <WithTimeline>
        <div class="flex flex-col gap-8">
            <template v-if="stands(items)">
                <StandDisplay :stands="items" />
            </template>
            <template v-else>
                <EventTimelineItem
                    v-for="event in items"
                    :key="event.id"
                    :event="event"
                />
            </template>
        </div>
    </WithTimeline>
</template>

<style scoped>
.selected {
    color: rgb(242, 147, 37);
    text-decoration: underline;
}
</style>
