<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type Event from "@/Types/Event";
import type Enrollment from "@/Types/Enrollment";
import { type User, isSpeaker, isCompany, isAdmin } from "@/Types/User";
import AppLayout from "@/Layouts/AppLayout.vue";
import SpeakerInfo from "@/Components/Event/SpeakerInfo.vue";
import Sponsor from "@/Components/Home/Sponsor.vue";
import Card from "@/Components/UI/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import { ArrowUp, ArrowDown, QrCode } from "@lucide/vue";

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

const formatMilestoneDate = (dayDate?: string | Date, timeStr?: string) => {
    if (!dayDate || !timeStr) return null;
    try {
        const d = dayDate instanceof Date ? dayDate : new Date(dayDate);
        if (isNaN(d.getTime())) return null;
        const day = d.getDate();
        const m = d
            .toLocaleDateString("pt-PT", {
                month: "short",
                timeZone: "Europe/Lisbon",
            })
            .replace(".", "");
        const month = m.charAt(0).toUpperCase() + m.slice(1);

        const isoTime = timeStr.includes("T")
            ? timeStr
            : `1970-01-01T${timeStr}.000000Z`;
        const timeObj = new Date(isoTime);
        const time = isNaN(timeObj.getTime())
            ? timeStr.slice(0, 5)
            : timeObj.toLocaleTimeString("pt-PT", {
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

const formattedSchedule = computed(() => {
    const start = formatMilestoneDate(
        props.event.event_day?.date,
        props.event.time_start,
    );
    const end = formatMilestoneDate(
        props.event.event_day?.date,
        props.event.time_end,
    );

    if (!start) return "";
    if (!end) return `${start.dateText}, ${start.timeText}`;
    return `${start.dateText}, ${start.timeText} — ${end.timeText}`;
});

const eventCategoryLabel = computed(() => {
    const typeName = props.event.type?.name?.toLowerCase();
    if (typeName === "talk" || typeName === "palestra") return "Palestra";
    if (typeName === "workshop") return "Workshop";
    if (typeName === "pitch") return "Pitch";
    if (typeName) {
        return typeName.charAt(0).toUpperCase() + typeName.slice(1);
    }
    return "Atividade";
});

const eventTypeColorClass = computed(() => {
    const typeName = props.event.type?.name?.toLowerCase();
    if (typeName === "talk" || typeName === "palestra") return "text-sky-400";
    if (typeName === "workshop") return "text-purple-400";
    return "text-amber-400";
});

const isEventFull = computed(() => {
    return Boolean(
        props.event.capacity &&
        props.enrollmentCount >= props.event.capacity &&
        !props.hasJoined,
    );
});

const cardTitle = computed(() => {
    if (props.hasJoined) return "Inscrição Confirmada";
    if (isEventFull.value) return "Vagas Esgotadas";
    if (!props.isEnrolled) return "Participa no Evento";
    if (
        props.canJoin &&
        (props.event.external_url || props.event.enroll_in_site)
    ) {
        return "Inscrições Abertas";
    }
    return "Entrada Livre";
});

const cardDescription = computed(() => {
    if (props.hasJoined) {
        return "O teu lugar neste evento está garantido. Vemo-nos lá!";
    }
    if (isEventFull.value) {
        return "Este evento atingiu a lotação máxima disponível.";
    }
    if (!props.isEnrolled) {
        return "A participação é gratuita para todos os inscritos na SINF 2026. Inscreve-te na conferência para teres acesso.";
    }
    if (props.canJoin && props.event.external_url) {
        return "As inscrições para este evento são realizadas através do formulário oficial.";
    }
    if (props.canJoin && props.event.enroll_in_site) {
        if (props.event.capacity) {
            const remaining = props.event.capacity - props.enrollmentCount;
            return `Ainda restam ${remaining} ${remaining === 1 ? "vaga disponível" : "vagas disponíveis"}. Garante o teu lugar.`;
        }
        return "As inscrições estão abertas para participantes da SINF 2026.";
    }
    return "Esta sessão tem acesso livre para todos os participantes inscritos na SINF 2026. Basta compareceres no local.";
});

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

const eventTabItems = computed<PillOption[]>(() => {
    return availableTabs.value.map((tab) => ({
        id: tab.key,
        label: tab.label,
        count: tab.count,
    }));
});

const scanCodeTabItems = computed<PillOption[]>(() => [
    {
        id: "scan-code",
        label: "Digitalizar QR Code",
        icon: QrCode,
        href: route("user.scan-code", {
            _query: {
                event: event.value.id,
            },
        }),
    },
]);

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
                            <span :class="eventTypeColorClass">{{
                                eventCategoryLabel
                            }}</span>

                            <template v-if="formattedSchedule">
                                <span class="text-neutral-600">·</span>
                                <span class="text-neutral-300">
                                    {{ formattedSchedule }}
                                </span>
                            </template>

                            <template v-if="event.location">
                                <span class="text-neutral-600">·</span>
                                <span class="text-neutral-400">
                                    {{ event.location }}
                                </span>
                            </template>
                        </div>

                        <h1
                            class="mt-4 text-3xl leading-[1.1] font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl"
                        >
                            {{ event.name }}
                        </h1>

                        <div
                            v-if="event.topic"
                            class="relative mt-4 flex items-start gap-3"
                        >
                            <p
                                class="text-base leading-snug font-medium text-neutral-200 sm:text-lg lg:text-xl"
                            >
                                {{ event.topic }}
                            </p>
                        </div>

                        <div
                            v-if="event.description_html"
                            class="prose prose-invert prose-headings:text-white prose-p:leading-relaxed prose-a:text-sinf-secondary-light hover:prose-a:text-white prose-strong:text-white mt-6 max-w-2xl text-left text-sm leading-relaxed font-normal text-neutral-400 sm:text-base"
                            v-html="event.description_html"
                        />
                        <p
                            v-else-if="event.description"
                            class="mt-6 max-w-2xl text-left text-sm leading-relaxed font-normal text-neutral-400 sm:text-base"
                        >
                            {{ event.description }}
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
                                {{ cardTitle }}
                            </h2>

                            <p
                                class="mt-2 text-xs leading-relaxed text-neutral-400 sm:text-sm"
                            >
                                {{ cardDescription }}
                            </p>

                            <div class="mt-6">
                                <PrimaryButton
                                    v-if="hasJoined"
                                    color="danger"
                                    class="w-full"
                                    padding="px-6 py-2.5"
                                    @click="handleLeaveEvent"
                                >
                                    <span>Cancelar Inscrição</span>
                                </PrimaryButton>

                                <PrimaryButton
                                    v-else-if="isEventFull"
                                    disabled
                                    color="pill"
                                    class="w-full"
                                    padding="px-6 py-2.5"
                                >
                                    <span>Vagas Esgotadas</span>
                                </PrimaryButton>

                                <PrimaryButton
                                    v-else-if="!isEnrolled"
                                    type="button"
                                    color="pill"
                                    class="w-full"
                                    padding="px-6 py-2.5"
                                    @click="handleEnrollSinf"
                                >
                                    <span>Inscrever-me na SINF</span>
                                </PrimaryButton>

                                <PrimaryButton
                                    v-else-if="canJoin && event.external_url"
                                    :href="event.external_url"
                                    external
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    color="pill"
                                    class="w-full"
                                    padding="px-6 py-2.5"
                                >
                                    <span>Inscrever-me</span>
                                </PrimaryButton>

                                <PrimaryButton
                                    v-else-if="canJoin && event.enroll_in_site"
                                    type="button"
                                    color="pill"
                                    class="w-full"
                                    padding="px-6 py-2.5"
                                    @click="handleJoinEvent"
                                >
                                    <span>Inscrever-me no Evento</span>
                                </PrimaryButton>

                                <PrimaryButton
                                    v-else-if="isEnrolled"
                                    disabled
                                    color="pill"
                                    class="w-full"
                                    padding="px-6 py-2.5"
                                >
                                    <span>Acesso Garantido</span>
                                </PrimaryButton>
                            </div>
                        </Card>
                    </div>
                </div>
            </header>

            <div
                v-if="availableTabs.length > 0 || isAdmin(authUser) || isStaff"
                class="flex flex-wrap items-center justify-center gap-3 sm:gap-4"
            >
                <PillSelector
                    v-if="availableTabs.length > 0"
                    v-model="activeTab"
                    :items="eventTabItems"
                    size="md"
                />

                <PillSelector
                    v-if="isAdmin(authUser) || isStaff"
                    :items="scanCodeTabItems"
                    size="md"
                    :wrap="false"
                />
            </div>

            <div
                v-if="activeTab === 'speakers'"
                class="w-full"
                :class="[
                    speakers.length === 1
                        ? 'mx-auto max-w-3xl'
                        : 'grid grid-cols-1 gap-6 lg:grid-cols-2',
                ]"
            >
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

                            <span
                                class="shrink-0 text-xs font-medium text-neutral-500"
                            >
                                Inscrito
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-12 text-center"
                >
                    <p class="text-sm text-neutral-400">
                        Ainda não existem participantes inscritos neste evento.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
