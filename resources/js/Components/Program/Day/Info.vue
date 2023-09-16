<script setup lang="ts">
import type EventDay from "@/Types/EventDay";
import { ref, onMounted, watch } from "vue";
import WithTimeline from "@/Components/Program/TimeLine/WithTimeline.vue";
import EventTimelineItem from "@/Components/Program/TimeLine/EventTimelineItem.vue";

interface Props {
    day: EventDay;
}

const selected = ref<HTMLElement | null>(null);

const toggle = ({ target }: MouseEvent) => {
    selected.value = target as HTMLElement;
};

watch(selected, (newValue, oldValue) => {
    oldValue?.classList.toggle("selected");
    newValue?.classList.toggle("selected");
});

onMounted(() => {
    selected.value = document.querySelector(
        "#tab-picker > button:first-of-type",
    ) as HTMLElement;
});

const { day } = defineProps<Props>();
</script>

<template>
    <section class="h-full w-full pt-8">
        <div
            id="tab-picker"
            class="flex flex-row justify-center gap-4 font-bold text-2023-teal"
        >
            <button class="transition" @click="toggle">Palestras</button>
            <button class="transition" @click="toggle">Atividades</button>
            <button class="transition" @click="toggle">Bancas</button>
        </div>
    </section>
    <WithTimeline>
        <div class="flex flex-col gap-8">
            <EventTimelineItem
                v-for="event in day.events"
                :key="event.id"
                :event="event"
            >
                Boas
            </EventTimelineItem>
        </div>
    </WithTimeline>
</template>

<style scoped>
.selected {
    color: rgb(242, 147, 37);
    text-decoration: underline;
}
</style>
