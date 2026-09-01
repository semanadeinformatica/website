<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import SpeakersCarousel from "@/Components/Home/SpeakersCarousel.vue";
import SponsorBanner from "@/Components/Home/SponsorBanner.vue";
import InfoPopup from "@/Components/Home/InfoPopup.vue";
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
    canEnroll: boolean;
}

defineProps<Props>();

const isAtBottom = ref(false);

function updateScrollState() {
    const scrollY = window.scrollY || window.pageYOffset;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    isAtBottom.value = scrollY + windowHeight >= documentHeight - 60;
}

function handleQuickScroll() {
    if (isAtBottom.value) {
        window.scrollTo({ top: 0, behavior: "smooth" });
        return;
    }

    const sections = Array.from(
        document.querySelectorAll<HTMLElement>("section"),
    );
    if (!sections.length) return;

    const currentY = window.scrollY || window.pageYOffset;
    const navOffset = 70;

    const nextSection = sections.find((section) => {
        const top = section.getBoundingClientRect().top + currentY;
        return top > currentY + navOffset + 20;
    });

    if (nextSection) {
        const targetY =
            nextSection.getBoundingClientRect().top + currentY - navOffset;
        window.scrollTo({ top: targetY, behavior: "smooth" });
    } else {
        window.scrollTo({
            top: document.documentElement.scrollHeight,
            behavior: "smooth",
        });
    }
}

onMounted(() => {
    updateScrollState();
    window.addEventListener("scroll", updateScrollState, { passive: true });
    window.addEventListener("resize", updateScrollState, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener("scroll", updateScrollState);
    window.removeEventListener("resize", updateScrollState);
});
</script>

<template>
    <AppLayout title="Home">
        <InfoPopup v-if="$page.props.auth.user && canEnroll" />

        <button
            type="button"
            :aria-label="
                isAtBottom ? 'Scroll to top' : 'Scroll to next section'
            "
            class="pill-container fixed right-6 bottom-6 z-40 h-11 w-11 justify-center text-neutral-300 shadow-none transition-all duration-200 hover:scale-110 hover:border-white/25 hover:text-white focus:outline-none active:scale-95 sm:right-8 sm:bottom-8 sm:h-12 sm:w-12"
            @click="handleQuickScroll"
        >
            <svg
                v-if="isAtBottom"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 10l7-7m0 0l7 7m-7-7v18"
                />
            </svg>
            <svg
                v-else
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 14l-7 7m0 0l-7-7m7 7V3"
                />
            </svg>
        </button>

        <section
            class="relative flex min-h-[calc(100vh-5rem)] flex-col items-center justify-center gap-8 px-4 py-16 text-center"
        >
            <div class="relative">
                <img
                    class="h-16 w-auto max-w-[85vw] object-contain drop-shadow-[0_0_20px_rgba(255,255,255,0.2)] transition-all duration-300 hover:brightness-110 sm:h-24 md:h-32"
                    src="/images/sinf2026.svg"
                    alt="Semana de Informática 2026"
                />
            </div>

            <p
                class="text-lg font-medium text-neutral-300 sm:text-xl md:text-2xl"
            >
                16 a 19 de novembro
            </p>

            <div v-if="canEnroll" class="pill-container">
                <button
                    type="button"
                    class="pill-item px-6 py-2 text-sm font-semibold sm:text-base"
                    @click="
                        $page.props.auth.user
                            ? router.put(route('enroll'))
                            : router.get(route('register'))
                    "
                >
                    Inscrever-me
                </button>
            </div>
        </section>

        <section
            id="aboutus"
            class="relative mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <div class="mb-10 flex justify-center">
                <div class="pill-container">
                    <span class="pill-item font-semibold text-white">
                        Sobre nós
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div
                    class="rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.01] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8"
                >
                    <p
                        class="text-justify text-base leading-relaxed text-neutral-300 sm:text-lg"
                    >
                        {{ $t("homePage.aboutUsText1") }}
                    </p>
                </div>
                <div
                    class="rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.01] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8"
                >
                    <p
                        class="text-justify text-base leading-relaxed text-neutral-300 sm:text-lg"
                    >
                        {{ $t("homePage.aboutUsText2") }}
                    </p>
                </div>
            </div>
        </section>

        <section class="relative mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mb-10 flex justify-center">
                <div class="pill-container">
                    <span class="pill-item font-semibold text-white">
                        Este ano temos
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-4">
                <div
                    v-if="days.length !== 0"
                    class="group flex flex-col items-center justify-center rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.02] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8"
                >
                    <span
                        class="text-3xl font-bold text-white transition-colors group-hover:text-neutral-100 sm:text-4xl"
                    >
                        {{ days.length }}
                    </span>
                    <span
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase transition-colors group-hover:text-neutral-300"
                    >
                        dias
                    </span>
                </div>

                <div
                    v-if="standCount !== 0"
                    class="group flex flex-col items-center justify-center rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.02] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8"
                >
                    <span
                        class="text-3xl font-bold text-white transition-colors group-hover:text-neutral-100 sm:text-4xl"
                    >
                        {{ standCount }}
                    </span>
                    <span
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase transition-colors group-hover:text-neutral-300"
                    >
                        bancas
                    </span>
                </div>

                <div
                    v-if="talkCount !== 0"
                    class="group flex flex-col items-center justify-center rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.02] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8"
                >
                    <span
                        class="text-3xl font-bold text-white transition-colors group-hover:text-neutral-100 sm:text-4xl"
                    >
                        {{ talkCount }}
                    </span>
                    <span
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase transition-colors group-hover:text-neutral-300"
                    >
                        palestras
                    </span>
                </div>

                <div
                    v-if="activityCount !== 0"
                    class="group flex flex-col items-center justify-center rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.02] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8"
                >
                    <span
                        class="text-3xl font-bold text-white transition-colors group-hover:text-neutral-100 sm:text-4xl"
                    >
                        {{ activityCount }}
                    </span>
                    <span
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase transition-colors group-hover:text-neutral-300"
                    >
                        atividades
                    </span>
                </div>
            </div>
        </section>

        <section id="speakers" class="relative w-full overflow-hidden py-20">
            <div class="mb-10 flex justify-center px-4">
                <div class="pill-container">
                    <span class="pill-item font-semibold text-white">
                        Oradores
                    </span>
                </div>
            </div>

            <template v-if="speakers.length !== 0">
                <div class="w-full px-2 sm:px-4 md:px-6">
                    <SpeakersCarousel :speakers="speakers" />
                </div>
            </template>
            <div v-else class="flex justify-center px-4">
                <div class="pill-container px-6 py-3 text-sm text-neutral-400">
                    Em breve...
                </div>
            </div>
        </section>

        <section
            id="sponsors"
            class="relative mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <div class="mb-10 flex justify-center">
                <div class="pill-container">
                    <span class="pill-item font-semibold text-white">
                        Patrocínios
                    </span>
                </div>
            </div>

            <div class="space-y-12">
                <SponsorBanner
                    v-for="(tier, idx) in sponsorTiers"
                    :key="tier.id"
                    :title="tier.name"
                    :sponsors="tier.sponsors ?? []"
                    :color="tier.color"
                    :idx="idx"
                />
            </div>
        </section>
    </AppLayout>
</template>
