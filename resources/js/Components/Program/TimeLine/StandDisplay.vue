<script setup lang="ts">
import type Stand from "@/Types/Stand";
import Sponsor from "@/Components/Home/Sponsor.vue";
import type { CompanyUser } from "@/Types/User";
import type SponsorTier from "@/Types/SponsorTier";
import { computed } from "vue";

interface Props {
    readonly stands: Stand[];
}

const props = defineProps<Props>();

const stands = computed(() => props.stands);

const standTiers = computed(() =>
    Object.fromEntries(
        stands.value.map((stand) => [
            stand.sponsor?.tier?.id,
            stand.sponsor?.tier,
        ]),
    ),
);

const standsPerTier = computed(() => {
  const grouped = stands.value.reduce((acc, stand) => {
    const tier = stand.sponsor?.tier;
    if (!tier) return acc;

    if (!acc.has(tier.id)) acc.set(tier.id, []);
    acc.get(tier.id)!.push(stand);

    return acc;
  }, new Map<SponsorTier["id"], Stand[]>());

  const sortedEntries = Array.from(grouped.entries()).sort(([idA], [idB]) => {
    const tierA = stands.value.find((s) => s.sponsor?.tier?.id === idA)?.sponsor?.tier;
    const tierB = stands.value.find((s) => s.sponsor?.tier?.id === idB)?.sponsor?.tier;
    return -((tierA?.rank ?? Infinity) - (tierB?.rank ?? Infinity));
  });

  return new Map(sortedEntries);
});
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
