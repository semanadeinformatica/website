<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type EventDay from "@/Types/EventDay";

export interface TabOption {
    id: string;
    label: string;
    count: number;
}

interface Props {
    days: EventDay[];
    currentDay: number;
    totalDays: number;
    eventDay?: EventDay;
    selectedType: string;
    availableTabs: TabOption[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: "select-type", type: string): void;
}>();

const formatShortDate = (dateStr?: Date | string): string => {
    if (!dateStr) return "";
    try {
        const d = new Date(dateStr);
        return d
            .toLocaleDateString("pt-PT", {
                day: "numeric",
                month: "short",
                timeZone: "Europe/Lisbon",
            })
            .replace(".", "");
    } catch {
        return "";
    }
};

interface DayItem {
    id: number;
    label: string;
    dateText?: string;
    active: boolean;
    href: string;
}

const dayItems = computed<DayItem[]>(() => {
    return Array.from({ length: props.totalDays }, (_, i) => {
        const dayIndex = i + 1;
        const d = props.days && props.days[i] ? props.days[i] : undefined;
        const dateText = d ? formatShortDate(d.date) : undefined;
        return {
            id: dayIndex,
            label: `Dia ${dayIndex}`,
            dateText,
            active: dayIndex === props.currentDay,
            href: route(route().current() ?? "program", { day: dayIndex }),
        };
    });
});

const handleSelectType = (id: string, event?: MouseEvent) => {
    emit("select-type", id);
    if (event?.currentTarget) {
        const el = event.currentTarget as HTMLElement;
        el.scrollIntoView({
            behavior: "smooth",
            inline: "center",
            block: "nearest",
        });
    }
};

const handleDayClick = (event?: MouseEvent) => {
    if (event?.currentTarget) {
        const el = event.currentTarget as HTMLElement;
        el.scrollIntoView({
            behavior: "smooth",
            inline: "center",
            block: "nearest",
        });
    }
};
</script>

<template>
    <div class="w-full">
        <div
            class="sticky top-16 z-30 -mx-4 mb-6 px-4 py-2.5 backdrop-blur-md sm:-mx-6 sm:px-6 lg:hidden"
        >
            <div
                v-if="eventDay?.theme"
                class="mb-2 flex items-center justify-between gap-3 border-b border-white/6 px-1 pb-2 text-left"
            >
                <span
                    class="text-[10px] font-semibold tracking-widest text-neutral-500 uppercase"
                >
                    Tema
                </span>
                <span class="truncate text-xs font-medium text-neutral-300">
                    {{ eventDay.theme }}
                </span>
            </div>

            <div
                v-if="totalDays > 1"
                class="pill-container no-scrollbar max-w-full flex-nowrap gap-1 overflow-x-auto p-1.5"
            >
                <Link
                    v-for="day in dayItems"
                    :key="day.id"
                    :href="day.href"
                    preserve-scroll
                    class="pill-item shrink-0 gap-1.5 text-xs whitespace-nowrap sm:text-sm"
                    :class="day.active ? 'pill-item-active' : ''"
                    @click="handleDayClick($event)"
                >
                    <span class="font-semibold">{{ day.label }}</span>
                    <template v-if="day.dateText">
                        <span class="opacity-35">•</span>
                        <span class="text-[11px] opacity-80">{{
                            day.dateText
                        }}</span>
                    </template>
                </Link>
            </div>

            <!-- Row 2: Category Filter Pills -->
            <div
                v-if="availableTabs.length > 1"
                class="pill-container no-scrollbar mt-2 max-w-full flex-nowrap gap-1 overflow-x-auto p-1.5"
            >
                <button
                    v-for="tab in availableTabs"
                    :key="tab.id"
                    type="button"
                    class="pill-item shrink-0 gap-2 text-xs whitespace-nowrap sm:text-sm"
                    :class="selectedType === tab.id ? 'pill-item-active' : ''"
                    @click="handleSelectType(tab.id, $event)"
                >
                    <span>{{ tab.label }}</span>
                    <span
                        class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold transition-colors"
                        :class="
                            selectedType === tab.id
                                ? 'bg-white/20 text-white'
                                : 'bg-white/10 text-neutral-400'
                        "
                    >
                        {{ tab.count }}
                    </span>
                </button>
            </div>
        </div>

        <nav
            class="no-scrollbar hidden max-h-[calc(100vh-7.5rem)] flex-col gap-4 overflow-y-auto rounded-3xl border border-white/8 bg-black/50 p-3 shadow-(--shadow-pill-inset) backdrop-blur-md lg:flex"
            aria-label="Programa do Evento"
        >
            <div
                v-if="eventDay?.theme"
                class="relative overflow-hidden rounded-2xl border border-white/8 bg-white/3 p-3.5 text-left transition-colors"
            >
                <div
                    class="pointer-events-none absolute -top-8 -right-8 h-20 w-20 rounded-full bg-white/4 blur-lg"
                />
                <div class="relative flex items-center justify-between gap-2">
                    <span
                        class="text-[10px] font-semibold tracking-widest text-neutral-500 uppercase"
                    >
                        Tema
                    </span>
                    <div class="flex items-center gap-1 opacity-40">
                        <span class="h-1 w-1 rounded-full bg-white" />
                        <span class="h-1 w-3 rounded-full bg-white" />
                        <span class="h-1 w-1 rounded-full bg-white" />
                    </div>
                </div>
                <p
                    class="relative mt-2 text-xs leading-relaxed font-semibold text-neutral-200"
                >
                    {{ eventDay.theme }}
                </p>
            </div>

            <div v-if="totalDays > 1" class="flex flex-col gap-1.5">
                <Link
                    v-for="day in dayItems"
                    :key="day.id"
                    :href="day.href"
                    preserve-scroll
                    :class="[
                        'group flex w-full cursor-pointer items-center justify-between gap-3 rounded-full px-4 py-2.5 text-left text-sm font-medium transition-all duration-200 select-none',
                        day.active
                            ? 'pill-item-active'
                            : 'text-neutral-300 hover:bg-white/8 hover:text-white active:scale-[0.98]',
                    ]"
                >
                    <span class="truncate">{{ day.label }}</span>
                    <span
                        v-if="day.dateText"
                        class="ml-auto shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold transition-colors"
                        :class="
                            day.active
                                ? 'bg-white/20 text-white'
                                : 'bg-white/10 text-neutral-400 group-hover:text-neutral-300'
                        "
                    >
                        {{ day.dateText }}
                    </span>
                </Link>
            </div>

            <div v-if="availableTabs.length > 1">
                <div
                    class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-neutral-500 uppercase"
                >
                    Filtrar
                </div>
                <div class="flex flex-col gap-1.5">
                    <button
                        v-for="tab in availableTabs"
                        :key="tab.id"
                        type="button"
                        :class="[
                            'group flex w-full cursor-pointer items-center justify-between gap-3 rounded-full px-4 py-2 text-left text-sm font-medium transition-all duration-200 select-none',
                            selectedType === tab.id
                                ? 'pill-item-active'
                                : 'text-neutral-300 hover:bg-white/8 hover:text-white active:scale-[0.98]',
                        ]"
                        @click="handleSelectType(tab.id)"
                    >
                        <span class="truncate">{{ tab.label }}</span>
                        <span
                            class="ml-auto shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold transition-colors"
                            :class="
                                selectedType === tab.id
                                    ? 'bg-white/20 text-white'
                                    : 'bg-white/10 text-neutral-400 group-hover:text-neutral-300'
                            "
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </div>
            </div>
        </nav>
    </div>
</template>
