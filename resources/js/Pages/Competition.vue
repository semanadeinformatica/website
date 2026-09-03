<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import Podium from "@/Components/Competition/Podium.vue";
import CompetitionLeaderboard from "@/Components/Competition/CompetitionLeaderboard.vue";
import type Competition from "@/Types/Competition";
import type { CompetitionPrizes } from "@/Types/Competition";
import { type User } from "@/Types/User";
import Card from "@/Components/UI/Card.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import { ExternalLink } from "@lucide/vue";

interface Props {
    competition: Competition;
    isParticipant: boolean;
    isEnrolled: boolean;
    isOver: boolean;
    prizes?: CompetitionPrizes[];
}

const props = withDefaults(defineProps<Props>(), {
    prizes: () => [],
});

const page = usePage();
const user = computed(() => page.props.auth?.user as User | undefined);

const competition = computed(() => props.competition);
const leaderboard = computed(() => competition.value.teams?.slice(0, 3) ?? []);

const formatDateRange = (startStr?: string, endStr?: string): string => {
    if (!startStr || !endStr) return "";
    try {
        const s = new Date(startStr);
        const e = new Date(endStr);
        const startDay = s.toLocaleDateString("pt-PT", {
            day: "numeric",
            month: "short",
            timeZone: "Europe/Lisbon",
        });
        const startTime = s.toLocaleTimeString("pt-PT", {
            hour: "2-digit",
            minute: "2-digit",
            timeZone: "Europe/Lisbon",
        });
        const endTime = e.toLocaleTimeString("pt-PT", {
            hour: "2-digit",
            minute: "2-digit",
            timeZone: "Europe/Lisbon",
        });
        const endDay = e.toLocaleDateString("pt-PT", {
            day: "numeric",
            month: "short",
            timeZone: "Europe/Lisbon",
        });

        if (startDay === endDay) {
            return `${startDay}, ${startTime} - ${endTime}`;
        }
        return `${startDay} ${startTime} — ${endDay} ${endTime}`;
    } catch {
        return "";
    }
};

const handleEnrollClick = () => {
    if (user.value) {
        router.put(route("enroll"));
    } else {
        router.get(route("register"));
    }
};

type CompetitionTab = "teams" | "prizes" | "regulation";

const hasTeams = computed(() => (competition.value.teams?.length ?? 0) > 0);
const hasPrizes = computed(() => (props.prizes?.length ?? 0) > 0);
const hasRegulation = computed(() =>
    Boolean(competition.value.regulation_html),
);

const availableTabs = computed<PillOption[]>(() => {
    const list: PillOption[] = [];
    if (hasTeams.value) {
        list.push({
            id: "teams",
            label: "Equipas",
            count: competition.value.teams?.length,
        });
    }
    if (hasPrizes.value) {
        list.push({
            id: "prizes",
            label: "Prémios",
            count: props.prizes?.length,
        });
    }
    if (hasRegulation.value) {
        list.push({
            id: "regulation",
            label: "Regulamento",
        });
    }
    return list;
});

const defaultTab = computed<CompetitionTab>(() => {
    if (hasTeams.value) return "teams";
    if (hasPrizes.value) return "prizes";
    return "regulation";
});

const activeTab = ref<CompetitionTab>(defaultTab.value);

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
    <AppLayout :title="competition.name">
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

        <div
            class="relative mx-auto max-w-5xl space-y-12 px-4 py-16 sm:space-y-16 sm:px-6 lg:px-8"
        >
            <header class="flex flex-col items-center gap-6 text-center">
                <div class="space-y-3">
                    <h1
                        class="text-3xl font-bold tracking-tight text-white sm:text-4xl md:text-5xl"
                    >
                        {{ competition.name }}
                    </h1>
                    <p
                        v-if="competition.theme"
                        class="text-base font-medium text-neutral-300 sm:text-lg md:text-xl"
                    >
                        {{ competition.theme }}
                    </p>
                </div>

                <div
                    class="mt-2 flex w-full flex-col gap-6 rounded-3xl border border-white/8 bg-black/50 p-6 text-left shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md sm:p-8"
                >
                    <div
                        v-if="competition.date_start && competition.date_end"
                        class="flex items-center gap-2 text-xs text-neutral-400 sm:text-sm"
                    >
                        <svg
                            class="h-4 w-4 text-neutral-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <span class="font-medium text-neutral-300">
                            {{
                                formatDateRange(
                                    competition.date_start,
                                    competition.date_end,
                                )
                            }}
                        </span>
                    </div>

                    <p
                        v-if="competition.description"
                        class="text-justify text-base leading-relaxed text-neutral-300 sm:text-lg"
                    >
                        {{ competition.description }}
                    </p>

                    <div class="border-t border-white/8 pt-6">
                        <div v-if="isOver" class="text-center sm:text-left">
                            <p class="text-xs text-neutral-400 sm:text-sm">
                                Esta competição já acabou. Obrigado pela
                                participação!
                            </p>
                        </div>

                        <div
                            v-else
                            class="flex flex-col items-center justify-between gap-4 sm:flex-row"
                        >
                            <template v-if="!isEnrolled">
                                <p
                                    class="text-center text-xs text-neutral-400 sm:text-left sm:text-sm"
                                >
                                    Precisas de estar inscrito na SINF 2026 para
                                    participar.
                                </p>
                                <button
                                    type="button"
                                    class="pill-container pill-item bg-sinf-primary/80 hover:bg-sinf-primary shrink-0 cursor-pointer px-6 py-2.5 text-xs font-semibold text-white transition-all active:scale-95 sm:text-sm"
                                    @click="handleEnrollClick"
                                >
                                    Inscrever-me na SINF
                                </button>
                            </template>

                            <template v-else-if="competition.registration_link">
                                <p
                                    class="text-center text-xs text-neutral-400 sm:text-left sm:text-sm"
                                >
                                    As inscrições para esta competição estão
                                    abertas.
                                </p>
                                <a
                                    :href="competition.registration_link"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="pill-container pill-item bg-sinf-secondary/80 hover:bg-sinf-secondary shrink-0 cursor-pointer gap-2 px-6 py-2.5 text-xs font-semibold text-white transition-all active:scale-95 sm:text-sm"
                                >
                                    <span>Inscrever Equipa</span>
                                    <ExternalLink :size="16" />
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </header>

            <div v-if="availableTabs.length > 0" class="flex justify-center">
                <PillSelector
                    v-model="activeTab"
                    :items="availableTabs"
                    size="md"
                />
            </div>

            <div v-if="activeTab === 'teams'" class="space-y-12 sm:space-y-16">
                <div v-if="isOver && leaderboard.length >= 3" class="space-y-4">
                    <Podium mode="winners" :leaderboard="leaderboard" />
                </div>

                <CompetitionLeaderboard :teams="competition.teams ?? []" />
            </div>

            <div v-else-if="activeTab === 'prizes'" class="space-y-8">
                <Podium mode="prizes" :prizes="prizes" />
            </div>

            <div v-else-if="activeTab === 'regulation'" class="w-full">
                <Card as="div" :interactive="false" padding="p-6 sm:p-10">
                    <div
                        class="prose prose-invert prose-headings:text-white prose-p:leading-relaxed prose-a:text-sinf-secondary-light hover:prose-a:text-white prose-strong:text-white max-w-none leading-relaxed wrap-break-word text-neutral-300"
                        v-html="competition.regulation_html"
                    />
                </Card>
            </div>

            <div
                v-if="availableTabs.length === 0"
                class="flex flex-col items-center justify-center py-20 text-center"
            >
                <div class="pill-container mb-4 px-6 py-2.5">
                    <span class="text-sm font-medium text-neutral-400">
                        Em breve...
                    </span>
                </div>
                <p class="max-w-md text-sm text-neutral-400">
                    Os detalhes e regulamento da competição estarão disponíveis
                    brevemente.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
