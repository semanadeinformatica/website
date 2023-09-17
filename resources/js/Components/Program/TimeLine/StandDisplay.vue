<script setup lang="ts">
import type Stand from "@/Types/Stand";
import StandTimelineItem from "./StandTimelineItem.vue";

interface Props {
    readonly stands: Stand[];
}

const { stands } = defineProps<Props>();

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
    <div class="flex flex-col gap-12">
        <section
            v-if="platSponsorStands.length > 0"
            id="plat"
            class="flex flex-col gap-3"
        >
            <span class="text-3xl font-bold" :class="shadowColorMap['PLATINUM']"
                >Platinum</span
            >
            <div class="flex flex-row gap-4">
                <StandTimelineItem
                    v-for="stand in platSponsorStands"
                    :key="stand.id"
                    :stand="stand"
                />
            </div>
        </section>
        <section
            v-if="goldSponsorStands.length > 0"
            id="gold"
            class="flex flex-col gap-3"
        >
            <span class="text-3xl font-bold" :class="shadowColorMap['GOLD']"
                >Gold</span
            >
            <div class="flex flex-row gap-4">
                <StandTimelineItem
                    v-for="stand in goldSponsorStands"
                    :key="stand.id"
                    :stand="stand"
                />
            </div>
        </section>
        <section
            v-if="silverSponsorStands.length > 0"
            id="silver"
            class="flex flex-col gap-3"
        >
            <span class="text-3xl font-bold" :class="shadowColorMap['SILVER']"
                >Silver</span
            >
            <div class="flex flex-row gap-4">
                <StandTimelineItem
                    v-for="stand in silverSponsorStands"
                    :key="stand.id"
                    :stand="stand"
                />
            </div>
        </section>
    </div>
</template>
