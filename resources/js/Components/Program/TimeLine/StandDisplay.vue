<script setup lang="ts">
import type Stand from "@/Types/Stand";
import Sponsor from "@/Components/Home/Sponsor.vue";
import type { CompanyUser } from "@/Types/User";
import type SponsorTier from "@/Types/SponsorTier";
import { computed } from "vue";

interface Props {
    readonly stands: Stand[];
}

const { stands } = defineProps<Props>();

const tiers = computed(() =>
    stands.reduce(
        (acc, stand) => {
            if (stand.sponsor && stand.sponsor.tier) {
                if (!acc[stand.sponsor.tier.id]) {
                    acc[stand.sponsor.tier.id] = stand.sponsor.tier;
                }
            }
            return acc;
        },
        {} as Record<SponsorTier["id"], SponsorTier>,
    ),
);

console.log(tiers);


const standsPerTier = computed(() =>
    stands.reduce(
        (acc, stand) => {
            if (stand.sponsor && stand.sponsor.tier) {
                if (acc[stand.sponsor.tier.id]) {
                    acc[stand.sponsor.tier.id].push(stand);
                } else {
                    acc[stand.sponsor.tier.id] = [stand];
                }
            }
            return acc;
        },
        {} as Record<SponsorTier["id"], Stand[]>,
    ),
);
</script>

<template>
    <div class="flex flex-col gap-12">
        <template v-for="tier_stands, tier_id in standsPerTier" :key="tier_id">
            <section v-if="tier_stands.length > 0" class="flex flex-col gap-3">
                <span class="text-3xl font-bold" :style="`color: ${tiers[tier_id].color}`">
                    {{ tiers[tier_id].name }}
                </span>
                <div class="flex flex-row flex-wrap gap-4">
                    <div
                        v-for="stand in tier_stands"
                        :key="stand.id"
                        class="w-48 border border-black shadow-lg"
                        :style="`shadow-color: ${tiers[tier_id].color}`"
                    >
                        <Sponsor
                            :company="(stand.sponsor?.company?.user as CompanyUser)"
                        >
                        </Sponsor>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>
