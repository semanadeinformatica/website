<script setup lang="ts">
import type Stand from "@/Types/Stand";
import Sponsor from "@/Components/Home/Sponsor.vue";
import type { CompanyUser } from "@/Types/User";
import type SponsorTier from "@/Types/SponsorTier";
import PillSelector from "@/Components/UI/PillSelector.vue";
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
    <div v-if="standsPerTier.size > 0" class="space-y-12 py-4">
        <template v-for="[tierId, tierStands] in standsPerTier" :key="tierId">
            <section v-if="tierStands.length > 0" class="w-full">
                <div class="mb-6 flex items-center justify-center">
                    <div class="pill-container gap-2.5 px-4 py-1.5 shadow-none">
                        <h3
                            class="text-xs font-semibold tracking-wider uppercase"
                            :style="{
                                color: standTiers[tierId]?.color || '#ffffff',
                            }"
                        >
                            {{ standTiers[tierId]?.name }}
                        </h3>
                    </div>
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

    <div
        v-else
        class="flex flex-col items-center justify-center py-16 text-center"
    >
        <PillSelector
            :items="[{ id: 'soon', label: 'Em breve...', disabled: true }]"
            size="sm"
            :wrap="false"
            container-class="mb-3"
        />
        <p class="max-w-md text-xs text-neutral-400 sm:text-sm">
            As bancas de empresas para esta edição serão anunciadas brevemente.
        </p>
    </div>
</template>
