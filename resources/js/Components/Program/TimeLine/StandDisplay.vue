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

const standsPerTier = computed(
    () =>
        stands.reduce((acc, stand) => {
            const sponsorTier = stand.sponsor?.tier;
            if (!sponsorTier) return acc;

            let hasTier = false;
            for (const tier of acc.keys())
                if (tier.id === sponsorTier.id) hasTier = true;
            
            if (!hasTier) acc.set(sponsorTier, []);

            acc.get(sponsorTier)?.push(stand);

            return acc;
        }, new Map<SponsorTier, Stand[]>()) ??
        ({} as Map<SponsorTier, Stand[]>),
);
</script>

<template>
    <div class="flex flex-col gap-12">
        <template
            v-for="[tier, tierStands] in standsPerTier"
            :key="tier.id"
        >
            <section v-if="tierStands.length > 0" class="flex flex-col gap-3">
                <span
                    class="text-3xl font-bold"
                    :style="`color: ${tier.color}`"
                >
                    {{ tier.name }}
                </span>
                <div class="flex flex-row flex-wrap gap-4">
                    <div
                        v-for="stand in tierStands"
                        :key="stand.id"
                        class="w-48 border border-black shadow-lg"
                        :style="`color: ${tier.color}`"
                    >
                        <Sponsor
                            :company="
                                stand.sponsor?.company?.user as CompanyUser
                            "
                        >
                        </Sponsor>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>
