<script setup lang="ts">
import { computed } from "vue";
import { route } from "ziggy-js";
import type EventDay from "@/Types/EventDay";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";

interface Props {
    days?: EventDay[];
    totalDays: number;
    queryDay: number;
    eventDay?: EventDay;
}

const props = defineProps<Props>();

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

const items = computed<PillOption[]>(() => {
    return Array.from({ length: props.totalDays }, (_, i) => {
        const dayIndex = i + 1;
        const d = props.days && props.days[i] ? props.days[i] : undefined;
        const dateText = d ? formatShortDate(d.date) : undefined;
        return {
            id: dayIndex,
            label: `Dia ${dayIndex}`,
            sublabel: dayIndex === props.queryDay ? dateText : undefined,
            href: route(route().current() ?? "program", { day: dayIndex }),
            active: dayIndex === props.queryDay,
        };
    });
});
</script>

<template>
    <section class="mb-5 flex flex-col items-center gap-4 text-center">
        <div v-if="totalDays > 1" class="flex justify-center">
            <PillSelector :items="items" size="md">
                <template #item="{ item, active }">
                    <span class="font-semibold">{{ item.label }}</span>
                    <template v-if="active && item.sublabel">
                        <span class="opacity-35">•</span>
                        <span class="text-xs opacity-85">{{
                            item.sublabel
                        }}</span>
                    </template>
                </template>
            </PillSelector>
        </div>

        <div v-if="eventDay" class="flex flex-col items-center gap-1.5">
            <p
                v-if="eventDay.theme"
                class="max-w-2xl text-sm leading-relaxed font-normal sm:text-base"
            >
                {{ eventDay.theme }}
            </p>
        </div>
    </section>
</template>
