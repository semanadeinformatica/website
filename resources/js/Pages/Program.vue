<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ProgramDayPanel from "@/Components/Program/ProgramDayPanel.vue";

const mockDays = [
    {
        theme: "Tema do dia 1",
    },
    {
        theme: "Tema do dia 2",
    },
    {
        theme: "Tema do dia 3",
    },
    {
        theme: "Tema do dia 4",
    },
    {
        theme: "Tema do dia 5",
    },
];

/**
 * The idea behind this code is that whenever the "day index" changes, we re-compute all the data needed to render the information about that day.
 * However, the watch runs before any relevant DOM elements exists, and as such this would never make the first option selected on page load.
 * By changing only the index on pageMount, we can circumvent this. The downside is that the initial value must be different from 0. -1 is a sane choice for this.
 */
// HACK: fix this if better solution found
const currentSelectedDayIdx = ref(-1);
onMounted(() => {
    currentSelectedDayIdx.value = 0;
});

const currentSelectedDay = computed(
    () =>
        currentSelectedDayIdx.value >= 0
            ? mockDays[currentSelectedDayIdx.value]
            : null, // First day as default - Nuno Pereira
);

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
        <div class="px-40 py-20">
            <section class="flex flex-col items-center gap-5">
                <ul
                    id="daySelection"
                    class="flex w-fit flex-row flex-wrap gap-4"
                >
                    <template v-for="(_, idx) in mockDays" :key="idx">
                        <li
                            class="inline-flex h-16 w-16 items-center justify-center rounded-sm bg-2023-teal-dark font-bold text-white"
                            @click="
                                () => {
                                    currentSelectedDayIdx = idx;
                                }
                            "
                        >
                            {{ idx + 1 }}
                        </li>
                    </template>
                </ul>
                <span class="text-2023-orange">23 de outubro</span>
                <p
                    class="mr-2 border border-solid border-black p-2.5 px-8 text-lg font-bold text-2023-teal shadow-md shadow-2023-teal"
                >
                    {{ currentSelectedDay?.theme }}
                </p>
            </section>
            <ProgramDayPanel :day="currentSelectedDay" />
        </div>
    </AppLayout>
</template>

<style>
.selected {
    background-color: rgb(242, 147, 37);
}
</style>
