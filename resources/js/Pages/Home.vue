<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
import QuickScroll from "@/Components/UI/QuickScroll.vue";
import InfoPopup from "@/Components/Home/InfoPopup.vue";
import AboutUsSection from "@/Components/Home/AboutUsSection.vue";
import StatsSection from "@/Components/Home/StatsSection.vue";
import SpeakersVerticalColumns from "@/Components/Home/SpeakersVerticalColumns.vue";
import SponsorsShowcase from "@/Components/Home/SponsorsShowcase.vue";
import EventCtaSection from "@/Components/Home/EventCtaSection.vue";
import { useSectionScroll } from "@/Composables/useSectionScroll";
import { useScrollReveal } from "@/Composables/useScrollReveal";
import type Edition from "@/Types/Edition";
import type EventDay from "@/Types/EventDay";
import type { User } from "@/Types/User";
import type SponsorTier from "@/Types/SponsorTier";

interface Props {
    edition: Edition;
    sponsorTiers: SponsorTier[];
    speakers: User[];
    days: EventDay[];
    activityCount: number;
    talkCount: number;
    standCount: number;
    competitionCount?: number;
    canEnroll: boolean;
}

withDefaults(defineProps<Props>(), {
    competitionCount: 0,
});

useSectionScroll();

const { targetRef: heroRef, isVisible: isHeroVisible } = useScrollReveal({
    threshold: 0.1,
    once: false,
});
</script>

<template>
    <AppLayout title="Home">
        <InfoPopup v-if="$page.props.auth.user && canEnroll" />

        <QuickScroll mode="sections" />

        <section
            id="hero"
            ref="heroRef"
            class="landing-section relative flex h-screen min-h-dvh max-h-dvh w-full flex-col items-center justify-center gap-8 px-4 text-center overflow-hidden pt-16"
        >
            <div
                class="relative transition-all duration-700 ease-in-out"
                :class="[
                    isHeroVisible
                        ? 'translate-y-0 opacity-100 scale-100'
                        : 'translate-y-12 opacity-0 scale-95',
                ]"
            >
                <img
                    class="h-16 w-auto max-w-[85vw] object-contain drop-shadow-[0_0_20px_rgba(255,255,255,0.2)] transition-all duration-300 hover:brightness-110 sm:h-24 md:h-32"
                    src="/images/sinf2026.svg"
                    alt="Semana de Informática 2026"
                />
            </div>

            <p
                class="text-lg font-medium text-neutral-300 transition-all duration-700 ease-in-out delay-150 sm:text-xl md:text-2xl"
                :class="[
                    isHeroVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0',
                ]"
            >
                16 a 19 de novembro
            </p>

            <div
                v-if="canEnroll"
                class="transition-all duration-700 ease-in-out delay-300"
                :class="[
                    isHeroVisible
                        ? 'translate-y-0 opacity-100 scale-100'
                        : 'translate-y-10 opacity-0 scale-95',
                ]"
            >
                <PillSelector
                    :items="[{ id: 'enroll', label: 'Inscrever-me', active: true }]"
                    size="md"
                    :wrap="false"
                    @select="
                        $page.props.auth.user
                            ? router.put(route('enroll'))
                            : router.get(route('register'))
                    "
                />
            </div>
        </section>

        <section
            id="aboutus"
            class="landing-section relative flex h-screen min-h-dvh max-h-dvh w-full flex-col items-center justify-center overflow-hidden px-4 sm:px-6 lg:px-8"
        >
            <AboutUsSection />
        </section>

        <section
            id="stats"
            class="landing-section relative flex h-screen min-h-dvh max-h-dvh w-full flex-col items-center justify-center overflow-hidden px-4 sm:px-6 lg:px-8"
        >
            <StatsSection
                :days="days"
                :stand-count="standCount"
                :talk-count="talkCount"
                :activity-count="activityCount"
                :competition-count="competitionCount"
            />
        </section>

        <section
            id="speakers"
            class="landing-section relative flex h-screen min-h-dvh max-h-dvh w-full flex-col items-center justify-center overflow-hidden"
        >
            <SpeakersVerticalColumns :speakers="speakers" />
        </section>

        <section
            id="sponsors"
            class="landing-section relative flex h-screen min-h-dvh max-h-dvh w-full flex-col items-center justify-center overflow-hidden"
        >
            <SponsorsShowcase :sponsor-tiers="sponsorTiers" />
        </section>

        <section
            id="cta"
            class="landing-section relative flex h-screen min-h-dvh max-h-dvh w-full flex-col items-center justify-center overflow-hidden px-4 sm:px-6 lg:px-8"
        >
            <EventCtaSection :can-enroll="canEnroll" />
        </section>
    </AppLayout>
</template>
