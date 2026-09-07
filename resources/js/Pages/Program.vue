<script setup lang="ts">
import { computed, ref, watch, onMounted, onBeforeUnmount } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ProgramSidebar, {
    type TabOption,
} from "@/Components/Program/ProgramSidebar.vue";
import ProgramTimeline from "@/Components/Program/ProgramTimeline.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
import type EventDay from "@/Types/EventDay";
import { ArrowUp } from "@lucide/vue";

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

const isScrolled = ref(false);

const updateScrollState = () => {
    isScrolled.value = (window.scrollY || window.pageYOffset) > 400;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
};

onMounted(() => {
    updateScrollState();
    window.addEventListener("scroll", updateScrollState, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener("scroll", updateScrollState);
});
</script>

<template>
    <AppLayout title="Programa">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2 scale-90"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-2 scale-90"
        >
            <button
                v-if="isScrolled"
                type="button"
                aria-label="Voltar ao topo"
                class="pill-container fixed right-6 bottom-6 z-40 h-11 w-11 cursor-pointer justify-center text-neutral-300 shadow-none transition-all duration-200 hover:scale-110 hover:border-white/25 hover:text-white focus:outline-none active:scale-95 sm:right-8 sm:bottom-8 sm:h-12 sm:w-12"
                @click="scrollToTop"
            >
                <ArrowUp :size="16" />
            </button>
        </Transition>

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
