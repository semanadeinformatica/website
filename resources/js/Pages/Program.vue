<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ProgramDayPanel from "@/Components/Program/ProgramDayPanel.vue";
import type EventDay from "@/Types/EventDay";
import route from "ziggy-js";

interface Props {
    eventDays: EventDay[];
}

const { eventDays } = defineProps<Props>();

// HACK: fix this if better solution found

/**
 * The idea behind this code is that whenever the "day index" changes, we re-compute all the data needed to render the information about that day.
 * However, the watch runs before any relevant DOM elements exists, and as such this would never make the first option selected on page load.
 * By changing only the index on pageMount, we can circumvent this. The downside is that the initial value must be different from 0. -1 is a sane choice for this.
 * One could argue that we don't need the index but this makes it easier for us to implement programmatic navigation: it's just a matter of incrementing or decrementing the index
 */
const currentSelectedDayIdx = ref(-1);
onMounted(() => {
    currentSelectedDayIdx.value = 0;
});

const currentSelectedDay = computed(
    () =>
        currentSelectedDayIdx.value >= 0
            ? eventDays[currentSelectedDayIdx.value]
            : null, // We might not have any days yet, use this as flag for fallback UI - Nuno Pereira
);

const updateURL = () => {
    history.pushState(
        null,
        "",
        route(route().current() ?? "program", {
            day: currentSelectedDayIdx.value + 1,
        }),
    );
};

watch(currentSelectedDayIdx, (newValue, oldValue) => {
    // See if this is faster using refs to the list items
    const previousItemSelector = `li:nth-of-type(${(oldValue ?? -1) + 1})`;
    const nextItemSelector = `li:nth-of-type(${newValue + 1})`;

    const itemContainer = document.querySelector("#daySelection");

    const previousItem = itemContainer?.querySelector(
        previousItemSelector,
    ) as HTMLElement;
    const nextItem = itemContainer?.querySelector(
        nextItemSelector,
    ) as HTMLElement;

    if (oldValue !== undefined) previousItem?.classList.toggle("selected");

    nextItem?.classList.toggle("selected");
});
</script>

<template>
    <AppLayout title="Programa">
        <div class="flex flex-col items-center px-40 py-20">
            <section class="flex flex-col items-center gap-5">
                <ul
                    id="daySelection"
                    class="flex w-fit flex-row flex-wrap gap-4"
                >
                    <template v-for="(_, idx) in eventDays" :key="idx">
                        <li
                            class="inline-flex h-16 w-16 items-center justify-center rounded-sm bg-2023-teal text-xl font-bold text-white transition"
                            @click="
                                () => {
                                    currentSelectedDayIdx = idx;
                                    updateURL();
                                }
                            "
                        >
                            {{ idx + 1 }}
                        </li>
                    </template>
                </ul>
                <span class="font-bold text-2023-orange">{{
                    new Intl.DateTimeFormat("pt-PT", {
                        month: "long",
                        day: "2-digit",
                    }).format(
                        new Date(currentSelectedDay?.date || Date.now()), // wtf - Nuno Pereira
                    )
                }}</span>
                <p
                    class="mr-2 max-w-2xl border border-solid border-black p-2.5 px-8 text-justify text-lg font-bold text-2023-teal shadow-md shadow-2023-teal"
                >
                    {{ currentSelectedDay?.theme || "Tema do dia" }}
                </p>
            </section>
            <ProgramDayPanel :day="currentSelectedDay" />
        </div>
    </AppLayout>
</template>

<style scoped>
.selected {
    background-color: rgb(242, 147, 37);
}
</style>
