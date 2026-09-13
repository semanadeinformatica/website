<script setup lang="ts">
import { ref, computed } from "vue";
import type SponsorTier from "@/Types/SponsorTier";
import type Sponsor from "@/Types/Sponsor";
import Modal from "@/Components/UI/Modal.vue";
import Card from "@/Components/UI/Card.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
import { ExternalLink } from "@lucide/vue";
import { useScrollReveal } from "@/Composables/useScrollReveal";

interface Props {
    sponsorTiers: SponsorTier[];
}

const props = defineProps<Props>();

const { targetRef, isVisible } = useScrollReveal({
    threshold: 0.15,
    once: false,
});

const selectedSponsor = ref<Sponsor | null>(null);
const selectedTier = ref<SponsorTier | null>(null);
const isModalOpen = ref(false);

function openSponsorModal(sponsor: Sponsor, tier: SponsorTier) {
    selectedSponsor.value = sponsor;
    selectedTier.value = tier;
    isModalOpen.value = true;
}

function closeSponsorModal() {
    isModalOpen.value = false;
    selectedSponsor.value = null;
    selectedTier.value = null;
}

// Flat list of all sponsors with their tier
const allSponsorsWithTier = computed(() => {
    const list: Array<{ sponsor: Sponsor; tier: SponsorTier }> = [];
    if (!props.sponsorTiers) return list;

    props.sponsorTiers.forEach((tier) => {
        tier.sponsors?.forEach((sponsor) => {
            list.push({ sponsor, tier });
        });
    });
    return list;
});

// Top tier (e.g. Platinum/Main) partners to feature prominently
const topTier = computed(() => {
    if (!props.sponsorTiers || props.sponsorTiers.length === 0) return null;
    return props.sponsorTiers.find((t) => t.sponsors && t.sponsors.length > 0) || null;
});

const topTierName = computed(() => topTier.value?.name || "Main Partners");

const topTierSponsors = computed(() => {
    if (!topTier.value) return [];
    return (topTier.value.sponsors || []).map((sponsor) => ({
        sponsor,
        tier: topTier.value!,
    }));
});

// Dual rows for infinite marquee streaming
const marqueeRow1 = computed(() => {
    const all = allSponsorsWithTier.value;
    if (all.length === 0) return [];
    const row = all.filter((_, idx) => idx % 2 === 0);
    const padded = [...row];
    while (padded.length < 8) {
        padded.push(...row);
    }
    return padded;
});

const marqueeRow2 = computed(() => {
    const all = allSponsorsWithTier.value;
    if (all.length === 0) return [];
    const row =
        all.length > 1
            ? all.filter((_, idx) => idx % 2 === 1)
            : [...all].reverse();
    const padded = [...row];
    while (padded.length < 8) {
        padded.push(...row);
    }
    return padded;
});
</script>

<template>
    <div
        ref="targetRef"
        class="relative mx-auto my-auto flex w-full max-w-7xl flex-col justify-center px-4 sm:px-6 lg:px-8"
    >
        <div class="mb-6 flex flex-col items-center text-center sm:mb-8">
            <h2
                class="mt-4 text-3xl tracking-tight text-white transition-all duration-700 ease-in-out delay-100 sm:text-5xl lg:text-6xl"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0',
                ]"
            >
                Empresas que tornam a SINF <span class="font-black">possível</span>
            </h2>

            <p
                class="mt-3 max-w-2xl text-sm leading-relaxed text-neutral-300 transition-all duration-700 ease-in-out delay-200 sm:text-base"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0',
                ]"
            >
                Equipas e organizações que marcam presença na FEUP para partilhar conhecimento técnico e recrutar talento universitário.
            </p>
        </div>

        <template v-if="allSponsorsWithTier.length > 0">
            <div v-if="topTierSponsors.length > 0" class="mb-6 sm:mb-8">
                <div
                    class="mb-3 flex items-center justify-center transition-all duration-700 ease-in-out delay-250"
                    :class="[
                        isVisible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0',
                    ]"
                >
                    <div class="pill-container px-3.5 py-1 shadow-none">
                        <span
                            class="text-xs font-semibold tracking-wider text-neutral-300 uppercase"
                        >
                            {{ topTierName }}
                        </span>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 sm:gap-6"
                >
                    <Card
                        v-for="(item, idx) in topTierSponsors.slice(0, 4)"
                        :key="`featured-${item.sponsor.id}`"
                        as="button"
                        padding="p-4 sm:p-5"
                        class="flex h-28 cursor-pointer items-center justify-center focus:outline-none transition-all duration-700 ease-in-out sm:h-32"
                        :style="{ transitionDelay: `${300 + idx * 100}ms` }"
                        :class="[
                            isVisible
                                ? 'translate-y-0 opacity-100 scale-100'
                                : 'translate-y-12 opacity-0 scale-95',
                        ]"
                        @click="openSponsorModal(item.sponsor, item.tier)"
                    >
                        <div class="flex h-14 w-full items-center justify-center p-1 sm:h-16">
                            <img
                                :src="item.sponsor.company?.user?.profile_photo_url"
                                :alt="item.sponsor.company?.user?.name"
                                class="max-h-full max-w-[85%] object-contain opacity-90 brightness-95 transition-all duration-300 group-hover:scale-105 group-hover:opacity-100 group-hover:brightness-110"
                                loading="lazy"
                            />
                        </div>
                    </Card>
                </div>
            </div>

            <div
                v-if="marqueeRow1.length > 0"
                class="marquee-mask relative w-full space-y-3 overflow-hidden py-1 transition-all duration-700 ease-in-out delay-500"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0',
                ]"
            >
                <div class="marquee-track-left flex items-center gap-4">
                    <div class="marquee-group flex items-center gap-4">
                        <Card
                            v-for="(item, idx) in marqueeRow1"
                            :key="`r1-g1-${item.sponsor.id}-${idx}`"
                            as="button"
                            padding="p-3 sm:p-4"
                            class="flex h-16 w-36 shrink-0 cursor-pointer items-center justify-center focus:outline-none sm:h-20 sm:w-48"
                            @click="openSponsorModal(item.sponsor, item.tier)"
                        >
                            <img
                                :src="item.sponsor.company?.user?.profile_photo_url"
                                :alt="item.sponsor.company?.user?.name"
                                class="max-h-8 max-w-[85%] object-contain opacity-75 grayscale transition-all duration-300 group-hover:grayscale-0 group-hover:opacity-100 sm:max-h-10"
                                loading="lazy"
                            />
                        </Card>
                    </div>
                    <div
                        class="marquee-group flex items-center gap-4"
                        aria-hidden="true"
                    >
                        <Card
                            v-for="(item, idx) in marqueeRow1"
                            :key="`r1-g2-${item.sponsor.id}-${idx}`"
                            as="button"
                            padding="p-3 sm:p-4"
                            class="flex h-16 w-36 shrink-0 cursor-pointer items-center justify-center focus:outline-none sm:h-20 sm:w-48"
                            @click="openSponsorModal(item.sponsor, item.tier)"
                        >
                            <img
                                :src="item.sponsor.company?.user?.profile_photo_url"
                                :alt="item.sponsor.company?.user?.name"
                                class="max-h-8 max-w-[85%] object-contain opacity-75 grayscale transition-all duration-300 group-hover:grayscale-0 group-hover:opacity-100 sm:max-h-10"
                                loading="lazy"
                            />
                        </Card>
                    </div>
                </div>

                <div class="marquee-track-right flex items-center gap-4">
                    <div class="marquee-group flex items-center gap-4">
                        <Card
                            v-for="(item, idx) in marqueeRow2"
                            :key="`r2-g1-${item.sponsor.id}-${idx}`"
                            as="button"
                            padding="p-3 sm:p-4"
                            class="flex h-16 w-36 shrink-0 cursor-pointer items-center justify-center focus:outline-none sm:h-20 sm:w-48"
                            @click="openSponsorModal(item.sponsor, item.tier)"
                        >
                            <img
                                :src="item.sponsor.company?.user?.profile_photo_url"
                                :alt="item.sponsor.company?.user?.name"
                                class="max-h-8 max-w-[85%] object-contain opacity-75 grayscale transition-all duration-300 group-hover:grayscale-0 group-hover:opacity-100 sm:max-h-10"
                                loading="lazy"
                            />
                        </Card>
                    </div>
                    <div
                        class="marquee-group flex items-center gap-4"
                        aria-hidden="true"
                    >
                        <Card
                            v-for="(item, idx) in marqueeRow2"
                            :key="`r2-g2-${item.sponsor.id}-${idx}`"
                            as="button"
                            padding="p-3 sm:p-4"
                            class="flex h-16 w-36 shrink-0 cursor-pointer items-center justify-center focus:outline-none sm:h-20 sm:w-48"
                            @click="openSponsorModal(item.sponsor, item.tier)"
                        >
                            <img
                                :src="item.sponsor.company?.user?.profile_photo_url"
                                :alt="item.sponsor.company?.user?.name"
                                class="max-h-8 max-w-[85%] object-contain opacity-75 grayscale transition-all duration-300 group-hover:grayscale-0 group-hover:opacity-100 sm:max-h-10"
                                loading="lazy"
                            />
                        </Card>
                    </div>
                </div>
            </div>
        </template>

        <div v-else class="flex justify-center py-10">
            <div
                class="pill-container px-6 py-2.5 font-mono text-xs text-neutral-400"
            >
                Em breve...
            </div>
        </div>

        <Modal :show="isModalOpen" max-width="md" @close="closeSponsorModal">
            <div
                v-if="selectedSponsor"
                class="relative flex flex-col items-center gap-6 text-center"
            >
                <div
                    class="flex h-20 w-36 items-center justify-center rounded-2xl border border-white/10 bg-black/60 p-3 shadow-inner backdrop-blur-md"
                >
                    <img
                        :src="selectedSponsor.company?.user?.profile_photo_url"
                        :alt="selectedSponsor.company?.user?.name"
                        class="max-h-full max-w-full object-contain"
                    />
                </div>

                <div class="flex w-full flex-col items-center gap-3">
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <h3 class="text-2xl font-bold text-white">
                            {{ selectedSponsor.company?.user?.name }}
                        </h3>
                        <div v-if="selectedTier" class="pill-container px-3 py-1 shadow-none">
                            <span class="text-xs font-semibold uppercase tracking-wider text-neutral-300">
                                {{ selectedTier.name }} Partner
                            </span>
                        </div>
                    </div>

                    <p
                        v-if="selectedSponsor.company?.description"
                        class="mt-2 text-sm leading-relaxed text-neutral-300"
                    >
                        {{ selectedSponsor.company.description }}
                    </p>

                    <div
                        v-if="selectedSponsor.company?.user?.usertype"
                        class="mt-4 flex justify-center border-t border-white/10 pt-4 w-full"
                    >
                        <a
                            :href="
                                selectedSponsor.company.user.usertype.toString()
                                    .startsWith('http')
                                    ? selectedSponsor.company.user.usertype.toString()
                                    : `https://${selectedSponsor.company.user.usertype}`
                            "
                            target="_blank"
                            rel="noopener noreferrer"
                            class="pill-container pill-item gap-1.5 px-5 py-2 text-xs font-semibold text-neutral-200 hover:text-white"
                        >
                            <span>Visitar Website</span>
                            <ExternalLink :size="13" />
                        </a>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.marquee-mask {
    mask-image: linear-gradient(
        to right,
        transparent 0%,
        black 10%,
        black 90%,
        transparent 100%
    );
    -webkit-mask-image: linear-gradient(
        to right,
        transparent 0%,
        black 10%,
        black 90%,
        transparent 100%
    );
}

.marquee-track-left {
    display: flex;
    width: max-content;
    animation: marqueeScrollLeft 35s linear infinite;
}

.marquee-track-right {
    display: flex;
    width: max-content;
    animation: marqueeScrollRight 38s linear infinite;
}

.marquee-track-left:hover,
.marquee-track-right:hover {
    animation-play-state: paused;
}

@keyframes marqueeScrollLeft {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

@keyframes marqueeScrollRight {
    0% {
        transform: translateX(-50%);
    }
    100% {
        transform: translateX(0);
    }
}
</style>
