<script setup lang="ts">
import { ref, computed } from "vue";
import type SponsorTier from "@/Types/SponsorTier";
import type Sponsor from "@/Types/Sponsor";
import Modal from "@/Components/UI/Modal.vue";
import Card from "@/Components/UI/Card.vue";
import ComingSoon from "@/Components/UI/ComingSoon.vue";
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

// Filter to tiers that contain at least one sponsor, preserving rank order
const populatedTiers = computed(() => {
    if (!props.sponsorTiers) return [];
    return props.sponsorTiers.filter(
        (tier) => tier.sponsors && tier.sponsors.length > 0,
    );
});

function getTierCardSize(rank: number) {
    if (rank >= 3) {
        return "h-24 w-44 sm:h-28 sm:w-56 p-4";
    }
    if (rank === 2) {
        return "h-20 w-36 sm:h-24 sm:w-48 p-3 sm:p-4";
    }
    return "h-16 w-32 sm:h-20 sm:w-40 p-2.5 sm:p-3";
}
</script>

<template>
    <div
        ref="targetRef"
        class="no-scrollbar relative mx-auto my-auto flex max-h-full w-full max-w-7xl flex-col items-center justify-center overflow-y-auto px-4 py-8 sm:px-6 lg:px-8"
    >
        <div class="mb-6 flex flex-col items-center text-center sm:mb-8">
            <h2
                class="mt-4 text-3xl tracking-tight text-white transition-all delay-100 duration-700 ease-in-out sm:text-5xl lg:text-6xl"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0',
                ]"
            >
                Empresas que tornam a SINF
                <span class="font-black">possível</span>
            </h2>

            <p
                class="mt-3 max-w-2xl text-sm leading-relaxed text-neutral-300 transition-all delay-200 duration-700 ease-in-out sm:text-base"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0',
                ]"
            >
                Equipas e organizações que marcam presença na FEUP para
                partilhar conhecimento técnico e recrutar talento universitário.
            </p>
        </div>

        <!-- Tiers Section -->
        <div
            v-if="populatedTiers.length > 0"
            class="flex w-full flex-col items-center space-y-8 sm:space-y-10"
        >
            <div
                v-for="(tier, tierIdx) in populatedTiers"
                :key="tier.id"
                class="flex w-full flex-col items-center transition-all duration-700 ease-in-out"
                :style="{ transitionDelay: `${250 + tierIdx * 100}ms` }"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-8 opacity-0',
                ]"
            >
                <!-- Tier Title -->
                <h3
                    class="mb-3 text-center text-xs font-semibold tracking-widest text-neutral-400 uppercase sm:mb-4 sm:text-sm"
                >
                    {{ tier.name }}
                </h3>

                <!-- Centered Sponsors Container -->
                <div
                    class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 lg:gap-6"
                >
                    <Card
                        v-for="sponsor in tier.sponsors"
                        :key="sponsor.id"
                        as="button"
                        layout="centered"
                        class="cursor-pointer focus:outline-none"
                        :class="getTierCardSize(tier.rank)"
                        @click="openSponsorModal(sponsor, tier)"
                    >
                        <img
                            :src="sponsor.company?.user?.profile_photo_url"
                            :alt="sponsor.company?.user?.name"
                            class="max-h-full max-w-full object-contain opacity-85 brightness-95 transition-all duration-300 group-hover:scale-105 group-hover:opacity-100 group-hover:brightness-110"
                            loading="lazy"
                        />
                    </Card>
                </div>
            </div>
        </div>

        <ComingSoon v-else class="py-12" />

        <!-- Clean, Simplified Sponsor Modal -->
        <Modal :show="isModalOpen" max-width="md" @close="closeSponsorModal">
            <div
                v-if="selectedSponsor"
                class="flex flex-col items-center text-center"
            >
                <img
                    :src="selectedSponsor.company?.user?.profile_photo_url"
                    :alt="selectedSponsor.company?.user?.name"
                    class="h-16 w-auto max-w-[200px] object-contain sm:h-20"
                />

                <div class="mt-4 flex flex-col items-center">
                    <h3 class="text-xl font-bold text-white sm:text-2xl">
                        {{ selectedSponsor.company?.user?.name }}
                    </h3>
                    <p
                        v-if="selectedTier"
                        class="mt-1 text-xs font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        {{ selectedTier.name }} Partner
                    </p>
                </div>

                <div
                    v-if="selectedSponsor.company?.description_html"
                    class="prose prose-invert prose-sm mt-5 max-h-60 w-full overflow-y-auto px-2 text-justify leading-relaxed text-neutral-300"
                    v-html="selectedSponsor.company.description_html"
                />
                <p
                    v-else-if="selectedSponsor.company?.description"
                    class="mt-5 text-sm leading-relaxed text-neutral-300"
                >
                    {{ selectedSponsor.company.description }}
                </p>

                <div
                    v-if="selectedSponsor.company?.social_media?.website"
                    class="mt-6 flex w-full justify-center border-t border-white/10 pt-4"
                >
                    <a
                        :href="
                            selectedSponsor.company.social_media.website.startsWith(
                                'http',
                            )
                                ? selectedSponsor.company.social_media.website
                                : `https://${selectedSponsor.company.social_media.website}`
                        "
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 text-sm font-medium text-neutral-300 transition-colors hover:text-white"
                    >
                        <span>Visitar Website</span>
                        <ExternalLink :size="14" />
                    </a>
                </div>
            </div>
        </Modal>
    </div>
</template>
