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
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import { ExternalLink, ArrowRight, ArrowUp, ArrowDown } from "@lucide/vue";

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

const formatMilestoneDate = (dateStr?: string) => {
    if (!dateStr) return null;
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return null;
        const day = d.getDate();
        const m = d
            .toLocaleDateString("pt-PT", {
                month: "short",
                timeZone: "Europe/Lisbon",
            })
            .replace(".", "");
        const month = m.charAt(0).toUpperCase() + m.slice(1);
        const time = d.toLocaleTimeString("pt-PT", {
            hour: "2-digit",
            minute: "2-digit",
            timeZone: "Europe/Lisbon",
        });
        return {
            dateText: `${day} ${month}`,
            timeText: time,
        };
    } catch {
        return null;
    }
};

const startDateInfo = computed(() =>
    formatMilestoneDate(props.competition.date_start),
);
const endDateInfo = computed(() =>
    formatMilestoneDate(props.competition.date_end),
);

const formattedSchedule = computed(() => {
    if (!startDateInfo.value || !endDateInfo.value) return "";
    const start = startDateInfo.value;
    const end = endDateInfo.value;

    if (start.dateText === end.dateText) {
        return `${start.dateText}, ${start.timeText} — ${end.timeText}`;
    }
    return `${start.dateText}, ${start.timeText} — ${end.dateText}, ${end.timeText}`;
});

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
            class="pill-container fixed right-6 bottom-6 z-40 h-11 w-11 cursor-pointer justify-center text-neutral-300 shadow-none transition-all duration-200 hover:scale-110 hover:border-white/25 hover:text-white focus:outline-none active:scale-95 sm:right-8 sm:bottom-8 sm:h-12 sm:w-12"
            @click="handleQuickScroll"
        >
            <ArrowUp v-if="isAtBottom" :size="16" />
            <ArrowDown v-else :size="16" />
        </button>

        <div
            class="relative mx-auto w-full max-w-7xl space-y-12 px-4 pt-4 pb-12 sm:space-y-16 sm:px-6 sm:pb-16 lg:px-8 lg:pt-8"
        >
            <header class="relative overflow-hidden pt-2 pb-6 sm:pt-4 sm:pb-10">

                <div
                    class="relative grid grid-cols-1 items-start gap-10 lg:grid-cols-12 lg:gap-12"
                >
                    <div
                        class="flex flex-col items-start text-left lg:col-span-7 xl:col-span-8"
                    >
                        <div
                            class="flex flex-wrap items-center gap-x-2.5 gap-y-1.5 text-xs font-semibold tracking-wider uppercase"
                        >
                            <span class="text-amber-400">Competição</span>

                            <template v-if="formattedSchedule">
                                <span class="text-neutral-600">·</span>
                                <span class="text-neutral-300">
                                    {{ formattedSchedule }}
                                </span>
                            </template>

                            <template v-if="isOver">
                                <span class="text-neutral-600">·</span>
                                <span class="text-neutral-500">Terminada</span>
                            </template>
                        </div>

                        <h1
                            class="mt-4 text-3xl leading-[1.1] font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl"
                        >
                            {{ competition.name }}
                        </h1>

                        <div
                            v-if="competition.theme"
                            class="relative mt-4 flex items-start gap-3"
                        >
                            <p
                                class="text-base leading-snug font-medium text-neutral-200 sm:text-lg lg:text-xl"
                            >
                                {{ competition.theme }}
                            </p>
                        </div>

                        <p
                            v-if="competition.description"
                            class="mt-6 max-w-2xl text-left text-sm leading-relaxed font-normal text-neutral-400 sm:text-base"
                        >
                            {{ competition.description }}
                        </p>
                    </div>

                    <div class="relative w-full lg:col-span-5 xl:col-span-4">
                        <Card
                            as="div"
                            :interactive="false"
                            padding="p-6 sm:p-7"
                            class="w-full"
                        >
                            <h2
                                class="text-xl font-bold tracking-tight text-white sm:text-2xl"
                            >
                                {{
                                    isOver
                                        ? "Competição Concluída"
                                        : isEnrolled &&
                                            competition.registration_link
                                          ? "Regista a tua Equipa"
                                          : "Participa na Competição"
                                }}
                            </h2>

                            <p
                                class="mt-2 text-xs leading-relaxed text-neutral-400 sm:text-sm"
                            >
                                <template v-if="isOver">
                                    Esta competição já terminou. Podes consultar
                                    a classificação e regulamento abaixo.
                                </template>
                                <template v-else-if="!isEnrolled">
                                    A participação é gratuita para todos os
                                    inscritos na SINF 2026. Inscreve-te na
                                    conferência para poder concorrer.
                                </template>
                                <template
                                    v-else-if="competition.registration_link"
                                >
                                    As inscrições de equipas estão abertas. Junta
                                    os teus colegas e submete a tua inscrição.
                                </template>
                                <template v-else>
                                    As inscrições de equipas para este desafio
                                    abrem muito em breve. Fica atento!
                                </template>
                            </p>

                            <div class="mt-6">
                                <PrimaryButton
                                    v-if="isOver"
                                    disabled
                                    color="pill"
                                    class="w-full"
                                    padding="px-6 py-2.5"
                                >
                                    Inscrições Encerradas
                                </PrimaryButton>

                                <template v-else>
                                    <PrimaryButton
                                        v-if="!isEnrolled"
                                        type="button"
                                        color="pill"
                                        class="w-full"
                                        padding="px-6 py-2.5"
                                        @click="handleEnrollClick"
                                    >
                                        <span>Inscrever-me na SINF</span>
                                    </PrimaryButton>

                                    <PrimaryButton
                                        v-else-if="
                                            competition.registration_link
                                        "
                                        :href="competition.registration_link"
                                        external
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        color="pill"
                                        class="w-full"
                                        padding="px-6 py-2.5"
                                    >
                                        <span>Inscrever Equipa</span>
                                    </PrimaryButton>
                                </template>
                            </div>
                        </Card>
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
                    <span class="text-xs text-neutral-400 sm:text-sm">
                        Não há informações adicionais disponíveis para esta
                        competição.
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
