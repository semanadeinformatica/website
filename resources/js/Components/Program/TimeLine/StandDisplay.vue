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

const standTiers = computed(() =>
    Object.fromEntries(
        stands.map((stand) => [stand.sponsor?.tier?.id, stand.sponsor?.tier]),
    ),
);

const standsPerTier = computed(
    () =>
        stands.reduce((acc, stand) => {
            const sponsorTier = stand.sponsor?.tier;
            if (!sponsorTier) return acc;

            if (!acc.has(sponsorTier.id)) acc.set(sponsorTier.id, []);

            acc.get(sponsorTier.id)?.push(stand);

            return acc;
        }, new Map<SponsorTier["id"], Stand[]>()) ??
        ({} as Map<SponsorTier["id"], Stand[]>),
);
</script>

<template>
    <div class="flex flex-col gap-12">
        <template v-for="[tierId, tierStands] in standsPerTier" :key="tierId">
            <section v-if="tierStands.length > 0" class="flex flex-col gap-3">
                <span
                    class="text-3xl font-bold"
                    :style="`color: ${standTiers[tierId].color}`"
                >
                    {{ standTiers[tierId].name }}
                </span>
                <div class="flex flex-row flex-wrap gap-4">
                    <div
                        v-for="stand in tierStands"
                        :key="stand.id"
                        class="w-48 border border-black shadow-lg"
                        :style="`color: ${standTiers[tierId].color}`"
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
