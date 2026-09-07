<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import SpeakersCarousel from "@/Components/Home/SpeakersCarousel.vue";
import SponsorBanner from "@/Components/Home/SponsorBanner.vue";
import InfoPopup from "@/Components/Home/InfoPopup.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
import { ArrowUp, ArrowDown } from "@lucide/vue";
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

const props = withDefaults(defineProps<Props>(), {
    competitionCount: 0,
});

const activeStatsCount = computed(() => {
    let count = 0;
    if (props.days?.length) count++;
    if (props.standCount) count++;
    if (props.talkCount) count++;
    if (props.activityCount) count++;
    if (props.competitionCount) count++;
    return count;
});

const statsGridColsClass = computed(() => {
    if (activeStatsCount.value >= 5) {
        return "grid-cols-2 sm:grid-cols-3 lg:grid-cols-5";
    }
    if (activeStatsCount.value === 4) {
        return "grid-cols-2 md:grid-cols-4";
    }
    if (activeStatsCount.value === 3) {
        return "grid-cols-1 sm:grid-cols-3";
    }
    return "grid-cols-2";
});

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
            class="pill-container fixed right-6 bottom-6 z-40 h-11 w-11 cursor-pointer justify-center text-neutral-300 shadow-none transition-all duration-200 hover:scale-110 hover:border-white/25 hover:text-white focus:outline-none active:scale-95 sm:right-8 sm:bottom-8 sm:h-12 sm:w-12"
            @click="handleQuickScroll"
        >
            <ArrowUp v-if="isAtBottom" :size="16" />
            <ArrowDown v-else :size="16" />
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

            <PillSelector
                v-if="canEnroll"
                :items="[{ id: 'enroll', label: 'Inscrever-me', active: true }]"
                size="md"
                :wrap="false"
                @select="
                    $page.props.auth.user
                        ? router.put(route('enroll'))
                        : router.get(route('register'))
                "
            />
        </section>

        <section
            id="aboutus"
            class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <div class="mb-10 flex justify-center">
                <PillSelector
                    :items="[
                        { id: 'aboutus', label: 'Sobre nós', active: true },
                    ]"
                    size="sm"
                    :wrap="false"
                />
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

        <section class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mb-10 flex justify-center">
                <PillSelector
                    :items="[
                        { id: 'stats', label: 'Este ano temos', active: true },
                    ]"
                    size="sm"
                    :wrap="false"
                />
            </div>

            <div
                :class="[
                    'grid gap-4 sm:gap-6',
                    statsGridColsClass,
                    {
                        '[&>*:last-child]:col-span-2 sm:[&>*:last-child]:col-span-1':
                            activeStatsCount % 2 !== 0,
                    },
                ]"
            >
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

                <div
                    v-if="competitionCount !== 0"
                    class="group flex flex-col items-center justify-center rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.02] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8"
                >
                    <span
                        class="text-3xl font-bold text-white transition-colors group-hover:text-neutral-100 sm:text-4xl"
                    >
                        {{ competitionCount }}
                    </span>
                    <span
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase transition-colors group-hover:text-neutral-300"
                    >
                        {{
                            competitionCount === 1
                                ? "competição"
                                : "competições"
                        }}
                    </span>
                </div>
            </div>
        </section>

        <section id="speakers" class="relative w-full overflow-hidden py-20">
            <div class="mb-10 flex justify-center px-4">
                <PillSelector
                    :items="[
                        { id: 'speakers', label: 'Oradores', active: true },
                    ]"
                    size="sm"
                    :wrap="false"
                />
            </div>

            <template v-if="speakers.length !== 0">
                <div class="w-full px-2 sm:px-4 md:px-6">
                    <SpeakersCarousel :speakers="speakers" />
                </div>
            </template>
            <div v-else class="flex justify-center px-4">
                <PillSelector
                    :items="[
                        { id: 'soon', label: 'Em breve...', disabled: true },
                    ]"
                    size="sm"
                    :wrap="false"
                />
            </div>
        </section>

        <section
            id="sponsors"
            class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <div class="mb-10 flex justify-center">
                <PillSelector
                    :items="[
                        { id: 'sponsors', label: 'Patrocínios', active: true },
                    ]"
                    size="sm"
                    :wrap="false"
                />
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
