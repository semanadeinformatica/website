<script setup lang="ts">
import type Stand from "@/Types/Stand";
import Sponsor from "@/Components/Home/Sponsor.vue";
import type { CompanyUser } from "@/Types/User";
import type SponsorTier from "@/Types/SponsorTier";
import ComingSoon from "@/Components/UI/ComingSoon.vue";
import { computed } from "vue";

interface Props {
    readonly stands: Stand[];
}

const props = defineProps<Props>();

const stands = computed(() => props.stands);

const standTiers = computed<Record<number, SponsorTier>>(() =>
    Object.fromEntries(
        stands.value
            .filter((stand) => stand.sponsor?.tier != null)
            .map((stand) => [stand.sponsor!.tier!.id, stand.sponsor!.tier!]),
    ),
);

const standsPerTier = computed(() => {
    const grouped = stands.value.reduce((acc, stand) => {
        const tier = stand.sponsor?.tier;
        if (!tier) return acc;

        if (!acc.has(tier.id)) acc.set(tier.id, []);
        acc.get(tier.id)!.push(stand);

        return acc;
    }, new Map<number, Stand[]>());

    const sortedEntries = Array.from(grouped.entries()).sort(([idA], [idB]) => {
        const tierA = standTiers.value[idA];
        const tierB = standTiers.value[idB];
        return -((tierA?.rank ?? 0) - (tierB?.rank ?? 0));
    });

    return new Map(sortedEntries);
});
</script>

<template>
    <div v-if="standsPerTier.size > 0" class="space-y-10 py-4 sm:space-y-12">
        <template v-for="[tierId, tierStands] in standsPerTier" :key="tierId">
            <section v-if="tierStands.length > 0" class="w-full">
                <div class="mb-4 flex items-center justify-center sm:mb-6">
                    <h3
                        class="text-xs font-semibold tracking-widest text-neutral-400 uppercase sm:text-sm"
                    >
                        {{ standTiers[tierId]?.name }}
                    </h3>
                </div>

                <div
                    class="flex flex-wrap items-center justify-center gap-4 sm:gap-6"
                >
                    <Sponsor
                        v-for="stand in tierStands"
                        :key="stand.id"
                        :company="stand.sponsor?.company?.user as CompanyUser"
                    />
                </div>
            </section>
        </template>
    </div>

    <ComingSoon v-else class="py-16" />
</template>
