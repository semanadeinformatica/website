<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Card from "@/Components/UI/Card.vue";
import Modal from "@/Components/UI/Modal.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import ParticipantSearch from "@/Components/Form/ParticipantSearch.vue";
import {
    AlertCircle,
    CheckCircle2,
    CircleDashed,
    Coins,
    LockKeyhole,
    Plus,
    RotateCcw,
    ScanLine,
    UserRoundSearch,
} from "@lucide/vue";

interface EditionSummary {
    id: number;
    name: string;
    year: string | number;
}

interface ParticipantSummary {
    id: number;
    name: string | null;
    email: string | null;
}

interface EnrollmentSummary {
    id: number;
    points: number;
}

interface QuestAwardItem {
    id: number;
    name: string;
    category: string;
    requirement_type: "event" | "stand" | "general";
    requirement_label: string;
    completed: boolean;
    available: boolean;
    unavailable_reason: string | null;
}

interface Props {
    edition?: EditionSummary | null;
    participant?: ParticipantSummary | null;
    enrollment?: EnrollmentSummary | null;
    quests: QuestAwardItem[];
    selectedParticipantId?: number | null;
}

const props = defineProps<Props>();

const participantId = ref(
    props.selectedParticipantId ? String(props.selectedParticipantId) : "",
);
const activeFilter = ref("all");
const confirmingQuest = ref<QuestAwardItem | null>(null);
const confirmOpen = ref(false);
const awardingQuestId = ref<number | null>(null);

const form = useForm({
    participant_id: "",
    quest_id: "",
});

const categoryLabels: Record<string, string> = {
    COMPANY: "Empresa",
    TALK: "Palestra",
    WORKSHOP: "Workshop",
    MILESTONE: "Milestone",
    TEAMBUILDING: "Team Building",
};

const categoryLabel = (category: string) =>
    categoryLabels[category] ?? category;

const pendingCount = computed(
    () =>
        props.quests.filter((quest) => !quest.completed && quest.available)
            .length,
);
const completedCount = computed(
    () => props.quests.filter((quest) => quest.completed).length,
);
const unavailableCount = computed(
    () =>
        props.quests.filter((quest) => !quest.completed && !quest.available)
            .length,
);
const completionPercentage = computed(() =>
    props.quests.length === 0
        ? 0
        : Math.round((completedCount.value / props.quests.length) * 100),
);

const filterOptions = computed<PillOption[]>(() => [
    { id: "all", label: "Todas", count: props.quests.length },
    { id: "pending", label: "Por atribuir", count: pendingCount.value },
    { id: "completed", label: "Concluídas", count: completedCount.value },
    {
        id: "unavailable",
        label: "Indisponíveis",
        count: unavailableCount.value,
    },
]);

const filteredQuests = computed(() => {
    switch (activeFilter.value) {
        case "pending":
            return props.quests.filter(
                (quest) => !quest.completed && quest.available,
            );
        case "completed":
            return props.quests.filter((quest) => quest.completed);
        case "unavailable":
            return props.quests.filter(
                (quest) => !quest.completed && !quest.available,
            );
        default:
            return props.quests;
    }
});

const awardError = computed(
    () => form.errors.quest_id ?? form.errors.participant_id ?? "",
);

const loadParticipant = (id: string) => {
    router.get(
        route("admin.questAwards.index"),
        { participant: id },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(participantId, (value) => {
    if (!value || value === String(props.participant?.id ?? "")) {
        return;
    }

    confirmOpen.value = false;
    loadParticipant(value);
});

watch(
    () => props.selectedParticipantId,
    (value) => {
        const id = value ? String(value) : "";
        if (id !== participantId.value) {
            participantId.value = id;
        }
    },
);

watch(
    () => props.participant?.id,
    () => {
        confirmOpen.value = false;
    },
);

watch(confirmOpen, (open) => {
    if (!open) {
        confirmingQuest.value = null;
        form.clearErrors();
    }
});

const clearParticipant = () => {
    participantId.value = "";
    router.get(
        route("admin.questAwards.index"),
        {},
        {
            preserveState: false,
            preserveScroll: true,
            replace: true,
        },
    );
};

const openConfirm = (quest: QuestAwardItem) => {
    confirmingQuest.value = quest;
    confirmOpen.value = true;
};

const confirmAward = () => {
    if (!props.participant || !confirmingQuest.value) {
        return;
    }

    form.participant_id = String(props.participant.id);
    form.quest_id = String(confirmingQuest.value.id);
    awardingQuestId.value = confirmingQuest.value.id;

    form.post(route("admin.questAwards.store"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirmOpen.value = false;
            form.reset();
        },
        onFinish: () => {
            awardingQuestId.value = null;
        },
    });
};
</script>

<template>
    <AdminLayout title="Validar Tarefas">
        <div class="flex flex-col gap-8">
            <header
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-white sm:text-3xl"
                    >
                        Validar Tarefas
                    </h1>
                    <p class="mt-1 text-sm text-neutral-400">
                        Atribui tarefas manualmente aos participantes, sem
                        precisar de ler o QR code.
                    </p>
                </div>

                <div
                    v-if="edition"
                    class="pill-container flex w-fit items-center gap-2 px-4 py-2 text-xs font-medium text-neutral-300"
                >
                    <ScanLine :size="14" class="text-neutral-400" />
                    <span>{{ edition.name }}</span>
                </div>
            </header>

            <Card
                as="section"
                :interactive="false"
                padding="p-4 sm:p-5"
                class="overflow-visible"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <ParticipantSearch v-model="participantId" />
                    </div>

                    <PrimaryButton
                        v-if="participant"
                        type="button"
                        color="pill"
                        @click="clearParticipant"
                    >
                        <RotateCcw :size="14" />
                        <span>Limpar seleção</span>
                    </PrimaryButton>
                </div>

                <p class="mt-2 text-xs text-neutral-500">
                    Pesquisa o participante por nome ou email. Se preferires,
                    também podes usar o
                    <span class="text-neutral-300">Perfil</span> do participante
                    para confirmar a inscrição.
                </p>
            </Card>

            <Card
                v-if="!participant"
                as="section"
                :interactive="false"
                padding="p-8 sm:p-12"
            >
                <div
                    class="flex flex-col items-center justify-center text-center"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full border border-white/10 bg-white/5 text-neutral-400"
                    >
                        <UserRoundSearch :size="30" />
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-white">
                        Seleciona um participante
                    </h2>
                    <p class="mt-2 max-w-md text-sm text-neutral-400">
                        Depois de escolheres um participante, vais poder
                        consultar as tarefas que já concluiu e atribuir as
                        restantes.
                    </p>
                </div>
            </Card>

            <template v-else>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div
                        class="rounded-3xl border border-white/8 bg-black/50 p-5 backdrop-blur-md"
                    >
                        <p
                            class="font-mono text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
                        >
                            Participante
                        </p>
                        <p
                            class="mt-3 truncate text-lg font-semibold text-white"
                        >
                            {{ participant.name ?? "Sem nome" }}
                        </p>
                        <p class="mt-1 truncate text-xs text-neutral-400">
                            {{ participant.email ?? "Sem email" }}
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/8 bg-black/50 p-5 backdrop-blur-md"
                    >
                        <p
                            class="font-mono text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
                        >
                            Pontos atuais
                        </p>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-3xl font-bold text-white">
                                {{ enrollment?.points ?? 0 }}
                            </span>
                            <img
                                src="/images/sinf-2026-sm.svg"
                                alt="SINFrão"
                                title="SINFrão"
                                class="h-5 w-5 object-contain"
                            />
                        </div>
                        <p class="mt-1 text-xs text-neutral-400">
                            Saldo na edição atual
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/8 bg-black/50 p-5 backdrop-blur-md"
                    >
                        <p
                            class="font-mono text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
                        >
                            Progresso
                        </p>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-3xl font-bold text-white">
                                {{ completionPercentage }}%
                            </span>
                            <span class="text-xs text-neutral-400">
                                {{ completedCount }} / {{ quests.length }}
                            </span>
                        </div>
                        <div
                            class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/10"
                        >
                            <div
                                class="h-full rounded-full bg-emerald-500/80 transition-all duration-500"
                                :style="{ width: `${completionPercentage}%` }"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="!enrollment"
                    class="flex items-start gap-3 rounded-3xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-200"
                >
                    <AlertCircle :size="18" class="mt-0.5 shrink-0" />
                    <div>
                        <p class="font-medium">
                            Este participante não está inscrito{{
                                edition ? ` no ${edition.name}` : ""
                            }}.
                        </p>
                        <p class="mt-1 text-xs text-amber-200/70">
                            Só é possível atribuir tarefas a participantes com
                            inscrição na edição atual.
                        </p>
                    </div>
                </div>

                <Card
                    v-else
                    as="section"
                    :interactive="false"
                    padding="p-4 sm:p-6"
                >
                    <div
                        class="flex flex-col gap-4 border-b border-white/8 pb-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2 class="text-lg font-semibold text-white">
                                Tarefas
                            </h2>
                            <p class="mt-1 text-xs text-neutral-400">
                                Consulta o estado de cada tarefa e atribui as
                                que faltam.
                            </p>
                        </div>

                        <PillSelector
                            v-model="activeFilter"
                            :items="filterOptions"
                            size="sm"
                            :wrap="true"
                        />
                    </div>

                    <div
                        v-if="quests.length === 0"
                        class="flex flex-col items-center justify-center py-12 text-center"
                    >
                        <CircleDashed :size="28" class="text-neutral-500" />
                        <p class="mt-3 text-sm font-medium text-white">
                            Não existem tarefas nesta edição.
                        </p>
                        <p class="mt-1 text-xs text-neutral-400">
                            Cria tarefas em Tarefas & Missões.
                        </p>
                    </div>

                    <div
                        v-else-if="filteredQuests.length === 0"
                        class="flex flex-col items-center justify-center py-12 text-center"
                    >
                        <CircleDashed :size="28" class="text-neutral-500" />
                        <p class="mt-3 text-sm font-medium text-white">
                            Nenhuma tarefa neste filtro.
                        </p>
                    </div>

                    <div v-else class="mt-4 space-y-2.5">
                        <div
                            v-for="quest in filteredQuests"
                            :key="quest.id"
                            class="flex flex-col gap-3 rounded-2xl border border-white/8 bg-white/[0.02] p-4 transition-colors hover:border-white/15 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex min-w-0 items-start gap-3">
                                <div
                                    class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        quest.completed
                                            ? 'border-emerald-500/30 bg-emerald-500/15 text-emerald-400'
                                            : quest.available
                                              ? 'border-white/10 bg-white/5 text-neutral-300'
                                              : 'border-amber-500/30 bg-amber-500/10 text-amber-300'
                                    "
                                >
                                    <CheckCircle2
                                        v-if="quest.completed"
                                        :size="16"
                                    />
                                    <LockKeyhole
                                        v-else-if="!quest.available"
                                        :size="15"
                                    />
                                    <CircleDashed v-else :size="16" />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-white"
                                    >
                                        {{ quest.name }}
                                    </p>
                                    <div
                                        class="mt-1.5 flex flex-wrap items-center gap-1.5"
                                    >
                                        <span
                                            class="pill-container px-2 py-0.5 text-[10px] font-semibold tracking-wide text-neutral-300 uppercase"
                                        >
                                            {{ categoryLabel(quest.category) }}
                                        </span>
                                        <span
                                            class="pill-container px-2 py-0.5 text-[10px] text-neutral-400"
                                        >
                                            {{ quest.requirement_label }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="quest.unavailable_reason"
                                        class="mt-1.5 text-xs text-amber-300/80"
                                    >
                                        {{ quest.unavailable_reason }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <span
                                    v-if="quest.completed"
                                    class="pill-container flex items-center gap-1.5 border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-300"
                                >
                                    <CheckCircle2 :size="13" />
                                    <span>Concluída</span>
                                </span>

                                <span
                                    v-else-if="!quest.available"
                                    class="pill-container flex items-center gap-1.5 border-amber-500/30 bg-amber-500/10 px-3 py-1.5 text-xs font-medium text-amber-200"
                                >
                                    <LockKeyhole :size="13" />
                                    <span>Indisponível</span>
                                </span>

                                <PrimaryButton
                                    v-else
                                    type="button"
                                    color="primary"
                                    :disabled="
                                        awardingQuestId === quest.id ||
                                        form.processing
                                    "
                                    @click="openConfirm(quest)"
                                >
                                    <Plus :size="14" />
                                    <span>Atribuir</span>
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </Card>
            </template>
        </div>

        <Modal
            v-model="confirmOpen"
            title="Atribuir tarefa?"
            :description="
                confirmingQuest
                    ? `Confirma a atribuição da tarefa a ${participant?.name ?? 'este participante'}.`
                    : undefined
            "
            max-width="md"
        >
            <div v-if="confirmingQuest" class="space-y-4">
                <div
                    class="rounded-2xl border border-white/10 bg-white/[0.03] p-4"
                >
                    <p class="text-sm font-semibold text-white">
                        {{ confirmingQuest.name }}
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                        <span
                            class="pill-container px-2 py-0.5 text-[10px] font-semibold tracking-wide text-neutral-300 uppercase"
                        >
                            {{ categoryLabel(confirmingQuest.category) }}
                        </span>
                        <span
                            class="pill-container px-2 py-0.5 text-[10px] text-neutral-400"
                        >
                            {{ confirmingQuest.requirement_label }}
                        </span>
                    </div>
                </div>

                <div
                    class="flex items-start gap-2.5 rounded-2xl border border-amber-500/20 bg-amber-500/8 p-3.5 text-xs text-amber-100/80"
                >
                    <Coins :size="16" class="mt-0.5 shrink-0 text-amber-300" />
                    <p>
                        Ao confirmar, a tarefa fica registada e os pontos
                        associados à conquista são atribuídos automaticamente ao
                        participante.
                    </p>
                </div>

                <p v-if="awardError" class="text-xs text-red-400">
                    {{ awardError }}
                </p>
            </div>

            <template #footer>
                <button
                    type="button"
                    class="pill-container cursor-pointer px-4 py-2 text-xs font-medium text-neutral-300 transition-all hover:border-white/20 hover:text-white active:scale-95"
                    :disabled="form.processing"
                    @click="confirmOpen = false"
                >
                    Cancelar
                </button>

                <PrimaryButton
                    type="button"
                    color="primary"
                    :disabled="form.processing"
                    @click="confirmAward"
                >
                    <span v-if="form.processing">A atribuir...</span>
                    <span v-else>Confirmar atribuição</span>
                </PrimaryButton>
            </template>
        </Modal>
    </AdminLayout>
</template>
