<script setup lang="ts">
import type Stand from "@/Types/Stand";
import Sponsor from "@/Components/Home/Sponsor.vue";
import type { CompanyUser } from "@/Types/User";
import type { Tier } from "@/Types/Sponsor";

interface Props {
    readonly stands: Stand[];
}

const { stands } = defineProps<Props>();

const textColorMap = {
    PLATINUM: "text-2023-red-dark",
    GOLD: "text-2023-orange",
    SILVER: "text-2023-teal-dark",
} as Record<Tier, string>;

const shadowColorMap = {
    PLATINUM: "shadow-2023-red-dark",
    GOLD: "shadow-2023-orange",
    SILVER: "shadow-2023-teal-dark",
} as Record<Tier, string>;

const silverSponsorStands = stands.filter(
    (stand) => stand.sponsor?.tier === "SILVER",
);
const goldSponsorStands = stands.filter(
    (stand) => stand.sponsor?.tier === "GOLD",
);
const platSponsorStands = stands.filter(
    (stand) => stand.sponsor?.tier === "PLATINUM",
);
</script>

<template>
    <div class="flex flex-col gap-12">
        <section
            v-if="platSponsorStands.length > 0"
            id="plat"
            class="flex flex-col gap-3"
        >
            <span class="text-3xl font-bold" :class="textColorMap['PLATINUM']"
                >Platinum</span
            >
            <div class="flex flex-row flex-wrap gap-4">
                <div
                    v-for="stand in platSponsorStands"
                    :key="stand.id"
                    class="w-48 border border-black shadow-lg"
                    :class="shadowColorMap['PLATINUM']"
                >
                    <Sponsor
                        :company="stand.sponsor?.company?.user as CompanyUser"
                    >
                    </Sponsor>
                </div>
            </div>
        </section>
        <section
            v-if="goldSponsorStands.length > 0"
            id="gold"
            class="flex flex-col gap-3"
        >
            <span class="text-3xl font-bold" :class="textColorMap['GOLD']"
                >Gold</span
            >
            <div class="flex flex-row flex-wrap gap-4">
                <div
                    v-for="stand in goldSponsorStands"
                    :key="stand.id"
                    class="w-48 border border-black shadow-lg"
                    :class="shadowColorMap['GOLD']"
                >
                    <Sponsor
                        :company="stand.sponsor?.company?.user as CompanyUser"
                    >
                    </Sponsor>
                </div>
            </div>
        </section>
        <section
            v-if="silverSponsorStands.length > 0"
            id="silver"
            class="flex flex-col gap-3"
        >
            <span class="text-3xl font-bold" :class="textColorMap['SILVER']"
                >Silver</span
            >
            <div class="flex flex-row flex-wrap gap-4">
                <div
                    v-for="stand in silverSponsorStands"
                    :key="stand.id"
                    class="w-48 border border-black shadow-lg"
                    :class="shadowColorMap['SILVER']"
                >
                    <Sponsor
                        :company="stand.sponsor?.company?.user as CompanyUser"
                    >
                    </Sponsor>
                </div>
            </div>
        </section>
    </div>
</template>
