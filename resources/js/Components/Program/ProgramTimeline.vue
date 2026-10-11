<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type EventDay from "@/Types/EventDay";
import { isSpeaker, type SpeakerUser } from "@/Types/User";
import Card from "@/Components/UI/Card.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
import StandDisplay from "@/Components/Program/StandDisplay.vue";
import { ArrowRight } from "@lucide/vue";

interface Props {
    eventDay: EventDay;
    selectedType: string;
}

const props = defineProps<Props>();

export type ItemType = "talk" | "activity" | "competition";

export interface UnifiedTimelineItem {
    id: string | number;
    rawId: number;
    type: ItemType;
    typeLabel: string;
    name: string;
    topic?: string;
    location?: string;
    capacity?: number;
    timeStart?: string;
    timeEnd?: string;
    startMinutes: number;
    durationMinutes?: number;
    users?: SpeakerUser[];
    url: string;
}

const parseToMinutes = (timeStr?: string): number => {
    if (!timeStr) return 9999;
    if (
        timeStr.includes("T") ||
        (timeStr.includes("-") && timeStr.includes(":"))
    ) {
        try {
            const d = new Date(timeStr);
            if (!isNaN(d.getTime())) {
                return d.getHours() * 60 + d.getMinutes();
            }
        } catch {
            // fallback
        }
    }
    const parts = timeStr.split(":");
    if (parts.length >= 2) {
        const h = parseInt(parts[0], 10) || 0;
        const m = parseInt(parts[1], 10) || 0;
        return h * 60 + m;
    }
    return 9999;
};

const formatTime = (timeStr?: string): string => {
    if (!timeStr) return "";
    if (
        timeStr.includes("T") ||
        (timeStr.includes("-") && timeStr.includes(":"))
    ) {
        try {
            const d = new Date(timeStr);
            if (!isNaN(d.getTime())) {
                return d.toLocaleTimeString("pt-PT", {
                    hour: "2-digit",
                    minute: "2-digit",
                    timeZone: "Europe/Lisbon",
                });
            }
        } catch {
            // fallback
        }
    }
    return timeStr.slice(0, 5);
};

const calculateDuration = (start?: string, end?: string): string | null => {
    if (!start || !end) return null;
    const sMin = parseToMinutes(start);
    const eMin = parseToMinutes(end);
    if (sMin === 9999 || eMin === 9999 || eMin <= sMin) return null;
    const diff = eMin - sMin;
    const hours = Math.floor(diff / 60);
    const mins = diff % 60;
    if (hours > 0 && mins > 0) return `${hours}h ${mins}m`;
    if (hours > 0) return `${hours}h`;
    return `${diff}m`;
};

const allUnifiedItems = computed<UnifiedTimelineItem[]>(() => {
    const list: UnifiedTimelineItem[] = [];

    if (props.eventDay.talks) {
        for (const t of props.eventDay.talks) {
            list.push({
                id: `talk-${t.id}`,
                rawId: t.id,
                type: "talk",
                typeLabel: "Palestra",
                name: t.name,
                topic: t.topic,
                location: t.location,
                timeStart: t.time_start,
                timeEnd: t.time_end,
                startMinutes: parseToMinutes(t.time_start),
                users: (t.users?.filter(isSpeaker) ?? []) as SpeakerUser[],
                url: route("event.show", { event: t.id }),
            });
        }
    }

    if (props.eventDay.activities) {
        for (const a of props.eventDay.activities) {
            list.push({
                id: `act-${a.id}`,
                rawId: a.id,
                type: "activity",
                typeLabel: "Workshop",
                name: a.name,
                topic: a.topic,
                location: a.location,
                capacity: a.capacity,
                timeStart: a.time_start,
                timeEnd: a.time_end,
                startMinutes: parseToMinutes(a.time_start),
                users: (a.users?.filter(isSpeaker) ?? []) as SpeakerUser[],
                url: route("event.show", { event: a.id }),
            });
        }
    }

    if (props.eventDay.competitions) {
        for (const c of props.eventDay.competitions) {
            list.push({
                id: `comp-${c.id}`,
                rawId: c.id,
                type: "competition",
                typeLabel: "Competição",
                name: c.name,
                topic: c.theme,
                timeStart: c.date_start,
                timeEnd: c.date_end,
                startMinutes: parseToMinutes(c.date_start),
                url: route("competition.show", { competition: c.slug }),
            });
        }
    }

    return list.sort((a, b) => a.startMinutes - b.startMinutes);
});

const filteredItems = computed<UnifiedTimelineItem[]>(() => {
    if (props.selectedType === "all") {
        return allUnifiedItems.value;
    }
    if (props.selectedType === "talk") {
        return allUnifiedItems.value.filter((item) => item.type === "talk");
    }
    if (props.selectedType === "activity") {
        return allUnifiedItems.value.filter((item) => item.type === "activity");
    }
    if (props.selectedType === "competitions") {
        return allUnifiedItems.value.filter(
            (item) => item.type === "competition",
        );
    }
    return allUnifiedItems.value;
});

interface TimeSlotGroup {
    timeKey: string;
    formattedStart: string;
    formattedEnd?: string;
    duration?: string | null;
    items: UnifiedTimelineItem[];
}

const timelineGroups = computed<TimeSlotGroup[]>(() => {
    const groups: TimeSlotGroup[] = [];
    for (const item of filteredItems.value) {
        const timeKey = item.timeStart ? formatTime(item.timeStart) : "00:00";
        const existing = groups.find((g) => g.timeKey === timeKey);
        if (existing) {
            existing.items.push(item);
        } else {
            const formattedStart = formatTime(item.timeStart);
            const formattedEnd = item.timeEnd
                ? formatTime(item.timeEnd)
                : undefined;
            const duration = calculateDuration(item.timeStart, item.timeEnd);
            groups.push({
                timeKey,
                formattedStart,
                formattedEnd,
                duration,
                items: [item],
            });
        }
    }
    return groups;
});
</script>

<template>
    <div class="w-full">
        <template v-if="selectedType === 'stand'">
            <StandDisplay :stands="eventDay.stands ?? []" />
        </template>

        <div
            v-else-if="filteredItems.length === 0"
            class="flex flex-col items-center justify-center py-20 text-center"
        >
            <PillSelector
                :items="[
                    {
                        id: 'none',
                        label: 'Sem eventos nesta categoria',
                        disabled: true,
                    },
                ]"
                size="sm"
                :wrap="false"
                container-class="mb-3"
            />
            <p class="text-xs text-neutral-500">
                Não existem eventos programados para este filtro no momento.
            </p>
        </div>

        <div v-else class="relative w-full">
            <div class="relative space-y-6 sm:space-y-8">
                <div
                    v-for="(group, groupIndex) in timelineGroups"
                    :key="group.timeKey"
                    class="group relative flex flex-col sm:flex-row sm:items-start"
                >
                    <div
                        class="hidden select-none sm:flex sm:w-28 sm:shrink-0 sm:flex-col sm:items-end sm:pt-3.5 sm:pr-6 sm:text-right"
                    >
                        <span
                            class="text-lg font-bold tracking-tight text-white"
                        >
                            {{ group.formattedStart }}
                        </span>
                        <span
                            v-if="group.formattedEnd"
                            class="mt-0.5 text-xs text-neutral-400"
                        >
                            {{ group.formattedEnd }}
                            <template v-if="group.duration">
                                <span class="text-neutral-600">·</span>
                                <span class="text-neutral-500">{{
                                    group.duration
                                }}</span>
                            </template>
                        </span>
                    </div>

                    <div
                        class="hidden sm:relative sm:flex sm:flex-col sm:items-center sm:self-stretch"
                    >
                        <div
                            class="z-10 mt-5 h-2.5 w-2.5 rounded-full bg-neutral-300 ring-4 ring-neutral-900 transition-all duration-200 group-hover:scale-125 group-hover:bg-white group-hover:ring-white/20"
                        />
                        <div
                            v-if="groupIndex < timelineGroups.length - 1"
                            class="absolute top-8 bottom-0 w-px bg-white/10"
                        />
                    </div>

                    <div
                        v-if="groupIndex < timelineGroups.length - 1"
                        class="absolute top-6 bottom-0 left-2.5 w-px bg-white/10 sm:hidden"
                    />
                    <div
                        class="absolute top-5 left-1.5 z-10 h-2.5 w-2.5 rounded-full bg-neutral-300 ring-4 ring-neutral-900 sm:hidden"
                    />

                    <div class="min-w-0 flex-1 pb-2 pl-7 sm:pb-0 sm:pl-6">
                        <div
                            :class="[
                                'grid gap-4',
                                group.items.length > 1
                                    ? 'grid-cols-1 md:grid-cols-2'
                                    : 'grid-cols-1',
                            ]"
                        >
                            <Card
                                v-for="item in group.items"
                                :key="item.id"
                                as="article"
                                padding="p-5 sm:p-6"
                                class="h-full w-full"
                            >
                                <template #header>
                                    <div
                                        class="mb-2.5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs"
                                    >
                                        <span
                                            class="font-semibold tracking-wider uppercase"
                                            :class="
                                                item.type === 'talk'
                                                    ? 'text-blue-400'
                                                    : item.type === 'activity'
                                                      ? 'text-purple-400'
                                                      : 'text-amber-400'
                                            "
                                        >
                                            {{ item.typeLabel }}
                                        </span>

                                        <template v-if="item.timeStart">
                                            <span
                                                class="text-neutral-600 sm:hidden"
                                                >·</span
                                            >
                                            <span
                                                class="text-neutral-400 sm:hidden"
                                            >
                                                {{ formatTime(item.timeStart)
                                                }}<template v-if="item.timeEnd">
                                                    -
                                                    {{
                                                        formatTime(item.timeEnd)
                                                    }}</template
                                                >
                                            </span>
                                        </template>

                                        <template v-if="item.location">
                                            <span class="text-neutral-600"
                                                >·</span
                                            >
                                            <span class="text-neutral-300">{{
                                                item.location
                                            }}</span>
                                        </template>

                                        <template v-if="item.capacity">
                                            <span class="text-neutral-600"
                                                >·</span
                                            >
                                            <span class="text-neutral-400"
                                                >{{ item.capacity }} vagas</span
                                            >
                                        </template>
                                    </div>

                                    <h3
                                        class="text-lg font-bold tracking-tight sm:text-xl"
                                    >
                                        <Link
                                            :href="item.url"
                                            class="group/link inline-flex items-center gap-2 text-white transition-colors hover:text-neutral-200"
                                            preserve-state
                                            preserve-scroll
                                        >
                                            <span class="leading-snug">{{
                                                item.name
                                            }}</span>
                                            <ArrowRight
                                                :size="15"
                                                class="shrink-0 text-neutral-500 transition-transform duration-200 group-hover/link:translate-x-1 group-hover/link:text-white"
                                            />
                                        </Link>
                                    </h3>
                                </template>

                                <p
                                    v-if="item.topic"
                                    class="text-sm leading-relaxed text-neutral-400"
                                >
                                    {{ item.topic }}
                                </p>

                                <template
                                    v-if="item.users && item.users.length > 0"
                                    #footer
                                >
                                    <div
                                        class="flex w-full flex-wrap items-center gap-3 border-t border-white/6 pt-3"
                                    >
                                        <div
                                            v-for="user in item.users"
                                            :key="user.id"
                                            class="flex items-center gap-2 text-xs"
                                        >
                                            <img
                                                v-if="user.profile_photo_url"
                                                :src="user.profile_photo_url"
                                                :alt="user.name"
                                                class="h-6 w-6 rounded-full object-cover ring-1 ring-white/15"
                                            />
                                            <div
                                                v-else
                                                class="flex h-6 w-6 items-center justify-center rounded-full bg-neutral-800 text-[10px] font-bold text-white ring-1 ring-white/10"
                                            >
                                                {{ user.name.charAt(0) }}
                                            </div>
                                            <div
                                                class="flex items-baseline gap-1"
                                            >
                                                <span
                                                    class="font-medium text-neutral-200"
                                                >
                                                    {{ user.name }}
                                                </span>
                                                <span
                                                    v-if="
                                                        user.usertype
                                                            ?.organization
                                                    "
                                                    class="text-neutral-500"
                                                >
                                                    ({{
                                                        user.usertype
                                                            .organization
                                                    }})
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
