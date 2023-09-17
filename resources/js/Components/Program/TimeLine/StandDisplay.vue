<script setup lang="ts">
import type Stand from "@/Types/Stand";
import StandTimelineItem from "./StandTimelineItem.vue";

interface Props {
    readonly stands: Stand[];
}

const { stands } = defineProps<Props>();

const times = {
    start: "09h00",
    end: "17h00",
};

const shadowColorMap = {
    PLATINUM: "text-2023-orange",
    GOLD: "text-2023-teal-dark",
    SILVER: "text-2023-red-dark",
} as Record<Stand["sponsor"]["tier"], string>;

const silverSponsorStands = stands.filter(
    (stand) => stand.sponsor.tier === "SILVER",
);
const goldSponsorStands = stands.filter(
    (stand) => stand.sponsor.tier === "GOLD",
);
const platSponsorStands = stands.filter(
    (stand) => stand.sponsor.tier === "PLATINUM",
);
</script>

<template>
    <div class="relative">
        <span class="text-2xl font-bold text-2023-orange">Início</span>
        <span
            class="absolute -left-[calc(2rem+0.85rem)] top-1/2 h-6 w-6 -translate-y-1/2 rounded-2xl bg-2023-orange"
        ></span>
        <span
            class="absolute -left-32 top-1/2 -translate-y-1/2 text-xl font-bold"
            >{{ times.start }}</span
        >
    </div>
    <div class="flex flex-col gap-4">
        <section
            v-if="platSponsorStands.length > 0"
            id="plat"
            class="flex flex-col gap-4"
        >
            <span class="text-3xl font-bold" :class="shadowColorMap['PLATINUM']"
                >Platinum</span
            >
            <StandTimelineItem
                v-for="stand in platSponsorStands"
                :key="stand.id"
                :stand="stand"
            />
        </section>
        <section
            v-if="goldSponsorStands.length > 0"
            id="gold"
            class="flex flex-col gap-4"
        >
            <span class="text-3xl font-bold" :class="shadowColorMap['GOLD']"
                >Gold</span
            >
            <StandTimelineItem
                v-for="stand in goldSponsorStands"
                :key="stand.id"
                :stand="stand"
            />
        </section>
        <section
            v-if="silverSponsorStands.length > 0"
            id="silver"
            class="flex flex-col gap-4"
        >
            <span class="text-3xl font-bold" :class="shadowColorMap['SILVER']"
                >Silver</span
            >
            <StandTimelineItem
                v-for="stand in silverSponsorStands"
                :key="stand.id"
                :stand="stand"
            />
        </section>
    </div>
    <div class="relative">
        <span class="text-2xl font-bold text-2023-orange">Fim</span>
        <span
            class="absolute -left-[calc(2rem+0.85rem)] top-1/2 h-6 w-6 -translate-y-1/2 rounded-2xl bg-2023-orange"
        ></span>
        <span
            class="absolute -left-32 top-1/2 -translate-y-1/2 text-xl font-bold"
            >{{ times.end }}</span
        >
    </div>
</template>
