<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from "vue";
import { router, Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type Event from "@/Types/Event";
import type Enrollment from "@/Types/Enrollment";
import { type User, isSpeaker, isCompany, isAdmin } from "@/Types/User";
import AppLayout from "@/Layouts/AppLayout.vue";
import SpeakerInfo from "@/Components/Event/SpeakerInfo.vue";
import Sponsor from "@/Components/Home/Sponsor.vue";

interface Props {
    event: Event;
    enrollments?: Enrollment[];
    enrollmentCount: number;
    canJoin: boolean;
    isParticipant: boolean;
    hasJoined: boolean;
    isEnrolled: boolean;
    isStaff: boolean;
}

const props = defineProps<Props>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user as User | undefined);

const event = computed(() => props.event);
const speakers = computed(() => event.value.users?.filter(isSpeaker) ?? []);
const companies = computed(() => event.value.users?.filter(isCompany) ?? []);

const formatDateTime = (
    dayDate?: string | Date,
    startStr?: string,
    endStr?: string,
): string => {
    let datePart = "";
    if (dayDate) {
        try {
            const d = dayDate instanceof Date ? dayDate : new Date(dayDate);
            datePart = d.toLocaleDateString("pt-PT", {
                day: "numeric",
                month: "short",
                timeZone: "Europe/Lisbon",
            });
        } catch {
            datePart = String(dayDate);
        }
    }

    const formatTimeOnly = (t?: string): string => {
        if (!t) return "";
        const isoString = t.includes("T") ? t : `1970-01-01T${t}.000000Z`;
        try {
            const d = new Date(isoString);
            return d.toLocaleTimeString("pt-PT", {
                hour: "2-digit",
                minute: "2-digit",
                timeZone: "Europe/Lisbon",
            });
        } catch {
            return t.slice(0, 5);
        }
    };

    const startTime = formatTimeOnly(startStr);
    const endTime = formatTimeOnly(endStr);

    if (datePart && startTime && endTime) {
        return `${datePart}, ${startTime} - ${endTime}`;
    }
    if (datePart && startTime) {
        return `${datePart}, ${startTime}`;
    }
    if (startTime && endTime) {
        return `${startTime} - ${endTime}`;
    }
    return datePart || startTime || "";
};

const handleJoinEvent = () => {
    router.put(route("event.join", { event: event.value.id }));
};

const handleLeaveEvent = () => {
    router.put(route("event.leave", { event: event.value.id }));
};

const handleEnrollSinf = () => {
    if (authUser.value) {
        router.put(route("enroll"));
    } else {
        router.get(route("register"));
    }
};

type EventTab = "speakers" | "companies" | "enrollments";

const hasSpeakers = computed(() => speakers.value.length > 0);
const hasCompanies = computed(() => companies.value.length > 0);
const hasEnrollments = computed(
    () =>
        (isAdmin(authUser.value) || props.isStaff) &&
        props.enrollments !== null &&
        props.enrollments !== undefined,
);

const availableTabs = computed(() => {
    const list: { key: EventTab; label: string; count?: number }[] = [];
    if (hasSpeakers.value) {
        list.push({
            key: "speakers",
            label: speakers.value.length === 1 ? "Orador" : "Oradores",
            count: speakers.value.length,
        });
    }
    if (hasCompanies.value) {
        list.push({
            key: "companies",
            label: "Empresas",
            count: companies.value.length,
        });
    }
    if (hasEnrollments.value) {
        list.push({
            key: "enrollments",
            label: "Inscrições",
            count: props.enrollmentCount,
        });
    }
    return list;
});

const defaultTab = computed<EventTab>(() => {
    if (hasSpeakers.value) return "speakers";
    if (hasCompanies.value) return "companies";
    return "enrollments";
});

const activeTab = ref<EventTab>(defaultTab.value);

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
    <AppLayout :title="event.name">
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
                        {{ event.name }}
                    </h1>
                    <p
                        v-if="event.topic"
                        class="text-base font-medium text-neutral-300 sm:text-lg md:text-xl"
                    >
                        {{ event.topic }}
                    </p>
                </div>

                <div
                    class="mt-2 flex w-full flex-col gap-6 rounded-3xl border border-white/8 bg-black/50 p-6 text-left shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md sm:p-8"
                >
                    <div
                        v-if="event.time_start || event.location || event.capacity"
                        class="flex flex-wrap items-center gap-4 text-xs text-neutral-400 sm:gap-6 sm:text-sm"
                    >
                        <div
                            v-if="event.time_start"
                            class="flex items-center gap-2"
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
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <span class="font-medium text-neutral-300">
                                {{
                                    formatDateTime(
                                        event.event_day?.date,
                                        event.time_start,
                                        event.time_end,
                                    )
                                }}
                            </span>
                        </div>

                        <div
                            v-if="event.location"
                            class="flex items-center gap-2"
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
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <span class="font-medium text-neutral-300">
                                {{ event.location }}
                            </span>
                        </div>

                        <div
                            v-if="event.capacity"
                            class="flex items-center gap-2"
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
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                            <span class="font-medium text-neutral-300">
                                {{ event.capacity }} vagas
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="event.description_html"
                        class="prose prose-invert max-w-none text-justify text-base leading-relaxed text-neutral-300 prose-headings:text-white prose-a:text-sinf-purple-light hover:prose-a:text-white prose-strong:text-white sm:text-lg"
                        v-html="event.description_html"
                    />
                    <p
                        v-else-if="event.description"
                        class="text-justify text-base leading-relaxed text-neutral-300 sm:text-lg"
                    >
                        {{ event.description }}
                    </p>

                    <div
                        v-if="isParticipant"
                        class="border-t border-white/8 pt-6"
                    >
                        <div
                            v-if="hasJoined"
                            class="flex flex-col items-center justify-between gap-4 sm:flex-row"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-semibold sm:text-sm"
                                >
                                    Inscrição confirmada! Vemo-nos lá.
                                </span>
                            </div>

                            <button
                                type="button"
                                class="pill-container pill-item border-red-500/20 bg-red-500/10 hover:bg-red-500/20 shrink-0 cursor-pointer px-5 py-2 text-xs font-semibold text-red-300 transition-all active:scale-95 sm:text-sm"
                                @click="handleLeaveEvent"
                            >
                                Cancelar Inscrição
                            </button>
                        </div>

                        <div
                            v-else-if="!isEnrolled"
                            class="flex flex-col items-center justify-between gap-4 sm:flex-row"
                        >
                            <p
                                class="text-center text-xs text-neutral-400 sm:text-left sm:text-sm"
                            >
                                Precisas de estar inscrito na SINF 2026 para
                                participar.
                            </p>
                            <button
                                type="button"
                                class="pill-container pill-item bg-sinf-crimson/80 hover:bg-sinf-crimson shrink-0 cursor-pointer px-6 py-2.5 text-xs font-semibold text-white transition-all active:scale-95 sm:text-sm"
                                @click="handleEnrollSinf"
                            >
                                Inscrever-me na SINF
                            </button>
                        </div>

                        <div
                            v-else-if="canJoin && event.external_url"
                            class="flex flex-col items-center justify-between gap-4 sm:flex-row"
                        >
                            <p
                                class="text-center text-xs text-neutral-400 sm:text-left sm:text-sm"
                            >
                                As inscrições são realizadas através do
                                formulário oficial.
                            </p>
                            <a
                                :href="event.external_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="pill-container pill-item bg-sinf-purple/80 hover:bg-sinf-purple shrink-0 cursor-pointer gap-2 px-6 py-2.5 text-xs font-semibold text-white transition-all active:scale-95 sm:text-sm"
                            >
                                <span>Inscrever-me</span>
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                    />
                                </svg>
                            </a>
                        </div>

                        <div
                            v-else-if="canJoin && event.enroll_in_site"
                            class="flex flex-col items-center justify-between gap-4 sm:flex-row"
                        >
                            <p
                                class="text-center text-xs text-neutral-400 sm:text-left sm:text-sm"
                            >
                                <span v-if="event.capacity">
                                    Ainda restam
                                    {{ event.capacity - enrollmentCount }} vagas
                                    disponíveis.
                                </span>
                                <span v-else>
                                    As inscrições estão abertas para
                                    participantes da SINF 2026.
                                </span>
                            </p>
                            <button
                                type="button"
                                class="pill-container pill-item bg-sinf-purple/80 hover:bg-sinf-purple shrink-0 cursor-pointer px-6 py-2.5 text-xs font-semibold text-white transition-all active:scale-95 sm:text-sm"
                                @click="handleJoinEvent"
                            >
                                Inscrever-me no Evento
                            </button>
                        </div>

                        <div
                            v-else-if="
                                event.capacity &&
                                enrollmentCount >= event.capacity
                            "
                            class="text-center sm:text-left"
                        >
                            <p
                                class="text-xs font-semibold text-red-400 sm:text-sm"
                            >
                                Este evento está atualmente esgotado.
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            <div
                v-if="availableTabs.length > 0 || (isAdmin(authUser) || isStaff)"
                class="flex flex-wrap items-center justify-center gap-3 sm:gap-4"
            >
                <div
                    v-if="availableTabs.length > 0"
                    class="pill-container flex-wrap justify-center gap-1 p-1"
                >
                    <button
                        v-for="tab in availableTabs"
                        :key="tab.key"
                        type="button"
                        class="pill-item cursor-pointer gap-2 px-5 py-2 text-xs font-semibold transition-all sm:text-sm"
                        :class="{ 'pill-item-active': activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        <span>{{ tab.label }}</span>
                        <span
                            v-if="tab.count !== undefined"
                            class="py-0.2 rounded-full px-1.5 text-[10px] transition-colors"
                            :class="
                                activeTab === tab.key
                                    ? 'bg-white/20 text-white'
                                    : 'bg-white/8 text-neutral-400'
                            "
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </div>

                <div
                    v-if="isAdmin(authUser) || isStaff"
                    class="pill-container p-1"
                >
                    <Link
                        :href="
                            route('user.scan-code', {
                                _query: {
                                    event: event.id,
                                },
                            })
                        "
                        class="pill-item cursor-pointer gap-2 px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-white/10 sm:text-sm"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                            />
                        </svg>
                        <span>Digitalizar QR Code</span>
                    </Link>
                </div>
            </div>

            <div v-if="activeTab === 'speakers'" class="space-y-6">
                <SpeakerInfo
                    v-for="speaker in speakers"
                    :key="speaker.id"
                    :user="speaker"
                />
            </div>

            <div
                v-else-if="activeTab === 'companies'"
                class="flex flex-row flex-wrap items-center justify-center gap-4 sm:gap-6"
            >
                <div v-for="(company, idx) in companies" :key="idx">
                    <Sponsor :company="company" />
                </div>
            </div>

            <div
                v-else-if="
                    activeTab === 'enrollments' &&
                    enrollments !== null &&
                    enrollments !== undefined
                "
                class="w-full space-y-6"
            >
                <div
                    v-if="enrollments.length > 0"
                    class="overflow-hidden rounded-3xl border border-white/8 bg-black/50 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md"
                >
                    <div class="divide-y divide-white/5">
                        <div
                            v-for="(enrollment, index) in enrollments"
                            :key="enrollment.id"
                            class="flex items-center justify-between gap-4 px-5 py-3.5 transition-colors hover:bg-white/4 sm:px-6 sm:py-4"
                        >
                            <div
                                class="flex min-w-0 items-center gap-4 sm:gap-6"
                            >
                                <span
                                    class="w-6 shrink-0 font-mono text-xs font-bold text-neutral-500 sm:w-8 sm:text-sm"
                                >
                                    #{{ index + 1 }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="truncate text-sm font-bold text-white sm:text-base"
                                    >
                                        {{
                                            enrollment.participant?.user
                                                ?.name ??
                                            `Participante #${enrollment.id}`
                                        }}
                                    </h4>
                                    <p
                                        v-if="
                                            enrollment.participant?.user?.email
                                        "
                                        class="truncate text-xs text-neutral-400"
                                    >
                                        {{
                                            enrollment.participant?.user?.email
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0">
                                <span
                                    class="pill-container border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-400 shadow-none"
                                >
                                    Inscrito
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-16 text-center"
                >
                    <p class="text-xs text-neutral-500 sm:text-sm">
                        Ainda nenhum participante se inscreveu neste evento.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
