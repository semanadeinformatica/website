<script setup lang="ts">
import { computed, ref, watch, onMounted } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from "@/Components/UI/Modal.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import Card from "@/Components/UI/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import type Quest from "@/Types/Quest";
import { QrcodeStream } from "vue-qrcode-reader";
import {
    ArrowLeft,
    HelpCircle,
    Camera,
    Keyboard,
    SwitchCamera,
    Zap,
    ZapOff,
    Play,
    Pause,
    CheckCircle2,
    AlertCircle,
    RotateCcw,
    Sparkles,
    Check,
} from "@lucide/vue";

interface Props {
    selectedQuest?: Quest;
    quests: Quest[];
}

const props = defineProps<Props>();

// Tab modes
type InputMode = "camera" | "manual";
const activeMode = ref<InputMode>("camera");

const modeOptions = computed<PillOption[]>(() => [
    {
        id: "camera",
        label: "Câmara",
        icon: Camera,
    },
    {
        id: "manual",
        label: "Manual",
        icon: Keyboard,
    },
]);

// Quest Selection
const selectedQuestId = ref<string>(
    props.selectedQuest?.id?.toString() ||
        (props.quests.length === 1 ? props.quests[0]?.id?.toString() : "") ||
        (props.quests.length > 0 ? props.quests[0]?.id?.toString() : ""),
);

const currentSelectedQuest = computed(() =>
    props.quests.find((q) => q.id.toString() === selectedQuestId.value),
);

// Form
const form = useForm({
    quest_code: "",
    quest: selectedQuestId.value,
});

watch(selectedQuestId, (newId) => {
    form.quest = newId;
});

// Camera state
const isCameraReady = ref(false);
const cameraFacingMode = ref<"environment" | "user">("environment");
const torchActive = ref(false);
const hasTorch = ref(false);
const isPaused = ref(false);
const cameraError = ref("");

// Scanner feedback state
type ScanStatus = "idle" | "processing" | "success" | "error";
const scanStatus = ref<ScanStatus>("idle");
const statusMessage = ref("");
const lastScannedCode = ref("");
const autoContinue = ref(true);
const countdown = ref<number | null>(null);
let countdownTimer: ReturnType<typeof setInterval> | null = null;

// Modal
const showInstructionsModal = ref(false);

const errorMap: Record<string, string> = {
    NotAllowedError:
        "Permissão de acesso à câmara recusada. Ativa o acesso nas definições do navegador.",
    NotFoundError: "Nenhuma câmara detetada neste dispositivo.",
    NotSupportedError:
        "Contexto seguro (HTTPS) necessário para aceder à câmara.",
    NotReadableError: "A câmara já está a ser utilizada por outra aplicação.",
    OverconstrainedError: "A câmara não cumpre os requisitos solicitados.",
    StreamApiNotSupportedError: "O teu navegador não suporta a Stream API.",
    InsecureContextError:
        "O acesso à câmara requer uma ligação segura (HTTPS).",
};

const onCameraError = (err: Error) => {
    isCameraReady.value = false;
    cameraError.value =
        errorMap[err.name] ||
        err.message ||
        "Não foi possível iniciar a câmara.";
};

const onCameraOn = (capabilities: MediaTrackCapabilities) => {
    isCameraReady.value = true;
    cameraError.value = "";
    hasTorch.value = Boolean(
        "torch" in capabilities &&
            (capabilities as { torch?: boolean }).torch,
    );
};

const onCameraOff = () => {
    isCameraReady.value = false;
    torchActive.value = false;
};

const toggleCameraFacing = () => {
    cameraFacingMode.value =
        cameraFacingMode.value === "environment" ? "user" : "environment";
};

const toggleTorch = () => {
    torchActive.value = !torchActive.value;
};

const togglePause = () => {
    isPaused.value = !isPaused.value;
};

const vibrate = (pattern: number | number[]) => {
    if (typeof navigator !== "undefined" && navigator.vibrate) {
        try {
            navigator.vibrate(pattern);
        } catch {
            // ignore if unsupported
        }
    }
};

const startCountdown = () => {
    if (!autoContinue.value) return;
    countdown.value = 3;
    if (countdownTimer) clearInterval(countdownTimer);

    countdownTimer = setInterval(() => {
        if (countdown.value === null) {
            if (countdownTimer) clearInterval(countdownTimer);
            return;
        }
        if (countdown.value <= 1) {
            if (countdownTimer) clearInterval(countdownTimer);
            countdown.value = null;
            resetScanner();
        } else {
            countdown.value--;
        }
    }, 1000);
};

const resetScanner = () => {
    if (countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
    }
    countdown.value = null;
    form.quest_code = "";
    scanStatus.value = "idle";
    statusMessage.value = "";
};

const triggerSubmit = (code: string) => {
    if (!form.quest) {
        scanStatus.value = "error";
        statusMessage.value =
            "Por favor seleciona uma tarefa antes de ler o código.";
        vibrate([100, 50, 100]);
        return;
    }

    const cleanedCode = code.trim();
    if (!cleanedCode) {
        scanStatus.value = "error";
        statusMessage.value = "Código inválido.";
        vibrate([100, 50, 100]);
        return;
    }

    form.quest_code = cleanedCode;
    lastScannedCode.value = cleanedCode;
    scanStatus.value = "processing";
    vibrate(80);

    form.post(route("quest.give", { quest: form.quest }), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            const flash = usePage().props.jetstream?.flash;
            if (flash?.bannerStyle === "danger") {
                scanStatus.value = "error";
                statusMessage.value =
                    flash?.banner || "Erro ao atribuir a tarefa.";
                vibrate([100, 50, 100]);
            } else {
                scanStatus.value = "success";
                statusMessage.value =
                    flash?.banner || "Tarefa atribuída com sucesso!";
                vibrate([120, 40, 160]);
                startCountdown();
            }
        },
        onError: (errs) => {
            scanStatus.value = "error";
            statusMessage.value =
                errs.quest_code ||
                errs.quest ||
                "Erro ao validar o código do participante.";
            vibrate([100, 50, 100]);
        },
    });
};

const onDetect = (detectedCodes: { rawValue: string }[]) => {
    if (
        isPaused.value ||
        scanStatus.value === "processing" ||
        scanStatus.value === "success"
    ) {
        return;
    }

    const first = detectedCodes[0];
    if (!first?.rawValue) return;

    triggerSubmit(first.rawValue);
};

const onManualSubmit = () => {
    if (!form.quest_code) return;
    triggerSubmit(form.quest_code);
};

onMounted(() => {
    // Check if initial quest is available
    if (props.quests.length > 0 && !selectedQuestId.value) {
        selectedQuestId.value = props.quests[0].id.toString();
    }
});
</script>

<template>
    <AppLayout title="Ler QR Code">
        <div
            class="relative mx-auto flex w-full max-w-5xl flex-1 flex-col px-4 py-8 sm:px-6 lg:px-8"
        >
            <div class="mb-8 flex justify-center sm:mb-10">
                <PillSelector size="md">
                    <template #leading>
                        <Link
                            :href="route('profile.show')"
                            class="pill-item cursor-pointer gap-2 px-4 py-2 text-xs font-medium text-neutral-400 transition-all duration-200 hover:bg-white/8 hover:text-white active:scale-95 sm:text-sm"
                        >
                            <ArrowLeft :size="16" class="shrink-0" />
                            <span>Voltar ao perfil</span>
                        </Link>
                    </template>

                    <button
                        type="button"
                        class="pill-item cursor-pointer gap-2 px-4 py-2 text-xs font-medium text-neutral-400 transition-all duration-200 hover:bg-white/8 hover:text-white active:scale-95 sm:text-sm"
                        @click="showInstructionsModal = true"
                    >
                        <HelpCircle :size="16" class="shrink-0" />
                        <span>Instruções</span>
                    </button>
                </PillSelector>
            </div>

            <Card
                as="section"
                :interactive="false"
                padding="p-4 sm:p-6 md:p-8"
                class="mx-auto w-full max-w-lg"
            >
                <div class="mb-6">
                    <div v-if="quests.length === 0">
                        <div
                            class="flex items-center gap-2.5 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-3.5 text-xs text-amber-200 sm:text-sm"
                        >
                            <AlertCircle :size="18" class="shrink-0" />
                            <span
                                >Nenhuma tarefa associada a esta edição de
                                momento.</span
                            >
                        </div>
                    </div>

                    <div v-else-if="quests.length === 1">
                        <div
                            class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-3.5"
                        >
                            <div class="flex items-center gap-3 truncate">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-sinf-primary/30 text-white"
                                >
                                    <Check :size="16" />
                                </div>
                                <div class="truncate">
                                    <p
                                        class="truncate text-sm font-semibold text-white"
                                    >
                                        {{ quests[0].name }}
                                    </p>
                                    <span
                                        v-if="quests[0].category"
                                        class="text-[11px] text-neutral-400 uppercase"
                                    >
                                        {{ quests[0].category }}
                                    </span>
                                </div>
                            </div>
                            <span
                                class="shrink-0 rounded-full border border-emerald-500/30 bg-emerald-500/15 px-2.5 py-0.5 text-[11px] font-medium text-emerald-300"
                            >
                                Selecionada
                            </span>
                        </div>
                    </div>

                    <div v-else>
                        <TextInput
                            id="quest-select"
                            v-model="selectedQuestId"
                            type="select"
                            :disabled="selectedQuest !== undefined"
                            :error-message="form.errors.quest"
                            class="w-full text-white"
                        >
                            <option
                                v-for="q in quests"
                                :key="q.id"
                                :value="q.id.toString()"
                                class="bg-neutral-900 text-white"
                            >
                                {{ q.name }} ({{ q.category }})
                            </option>
                        </TextInput>
                    </div>
                </div>

                <div class="mb-5 flex justify-center">
                    <PillSelector
                        v-model="activeMode"
                        :items="modeOptions"
                        size="sm"
                        :wrap="false"
                    />
                </div>

                <div v-show="activeMode === 'camera'" class="w-full">
                    <div
                        class="relative mx-auto aspect-square w-full max-w-xs overflow-hidden rounded-3xl border transition-all duration-300 sm:max-w-sm"
                        :class="[
                            scanStatus === 'success'
                                ? 'border-emerald-500'
                                : scanStatus === 'error'
                                  ? 'border-red-500'
                                  : 'border-white/15 bg-black/90',
                        ]"
                    >
                        <QrcodeStream
                            :constraints="{ facingMode: cameraFacingMode }"
                            :torch="torchActive"
                            :paused="
                                isPaused ||
                                scanStatus === 'processing' ||
                                scanStatus === 'success'
                            "
                            class="h-full w-full object-cover"
                            @camera-on="onCameraOn"
                            @camera-off="onCameraOff"
                            @detect="onDetect"
                            @error="onCameraError"
                        >
                            <div
                                v-if="!isCameraReady && !cameraError"
                                class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/95 p-4 text-center"
                            >
                                <div
                                    class="h-8 w-8 animate-spin rounded-full border-2 border-white/20 border-t-sinf-primary-light"
                                />
                                <p class="text-xs text-neutral-300">
                                    A inicializar câmara...
                                </p>
                            </div>

                            <div
                                v-if="cameraError"
                                class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-neutral-950/95 p-6 text-center"
                            >
                                <p class="text-xs text-red-300">
                                    {{ cameraError }}
                                </p>
                                <div class="mt-2 flex flex-col gap-2">
                                    <button
                                        type="button"
                                        class="rounded-xl border border-white/10 bg-white/10 px-3.5 py-1.5 text-xs font-medium text-white transition-all hover:bg-white/20"
                                        @click="
                                            () => {
                                                cameraError = '';
                                                isCameraReady = false;
                                            }
                                        "
                                    >
                                        Tentar novamente
                                    </button>
                                    <button
                                        type="button"
                                        class="text-xs text-neutral-400 underline hover:text-white"
                                        @click="activeMode = 'manual'"
                                    >
                                        Inserir código manualmente
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="
                                    isCameraReady &&
                                    scanStatus !== 'success' &&
                                    scanStatus !== 'error'
                                "
                                class="pointer-events-none absolute inset-0"
                            >
                                <!-- Corner Brackets -->
                                <div
                                    class="absolute top-5 left-5 h-8 w-8 rounded-tl-xl border-t-3 border-l-3 border-white/80"
                                />
                                <div
                                    class="absolute top-5 right-5 h-8 w-8 rounded-tr-xl border-t-3 border-r-3 border-white/80"
                                />
                                <div
                                    class="absolute bottom-5 left-5 h-8 w-8 rounded-bl-xl border-b-3 border-l-3 border-white/80"
                                />
                                <div
                                    class="absolute right-5 bottom-5 h-8 w-8 rounded-br-xl border-r-3 border-b-3 border-white/80"
                                />

                                <!-- Scanning Laser Line (clean, no shadow) -->
                                <div
                                    v-if="!isPaused"
                                    class="laser-line pointer-events-none absolute inset-x-8 h-0.5 bg-gradient-to-r from-transparent via-sinf-primary-light to-transparent"
                                />
                            </div>

                            <!-- Success / Processing / Error Overlay -->
                            <transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                            >
                                <div
                                    v-if="
                                        scanStatus === 'processing' ||
                                        scanStatus === 'success' ||
                                        scanStatus === 'error'
                                    "
                                    class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/80 p-5 text-center backdrop-blur-sm"
                                >
                                    <!-- Processing State -->
                                    <template
                                        v-if="scanStatus === 'processing'"
                                    >
                                        <div
                                            class="h-10 w-10 animate-spin rounded-full border-3 border-white/20 border-t-sinf-primary-light"
                                        />
                                        <p
                                            class="text-sm font-semibold text-white"
                                        >
                                            A validar tarefa...
                                        </p>
                                        <p class="font-mono text-xs text-white">
                                            {{ lastScannedCode }}
                                        </p>
                                    </template>

                                    <!-- Success State -->
                                    <template
                                        v-else-if="scanStatus === 'success'"
                                    >
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400 ring-2 ring-emerald-500/50"
                                        >
                                            <CheckCircle2 :size="32" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-base font-bold text-white"
                                            >
                                                {{ statusMessage }}
                                            </p>
                                            <p
                                                class="mt-1 font-mono text-xs text-neutral-300"
                                            >
                                                Código: {{ lastScannedCode }}
                                            </p>
                                        </div>

                                        <div
                                            class="mt-2 flex flex-col items-center gap-2"
                                        >
                                            <PrimaryButton
                                                type="button"
                                                color="primary"
                                                padding="px-5 py-2"
                                                @click="resetScanner"
                                            >
                                                <RotateCcw :size="14" />
                                                <span>Ler Próximo</span>
                                                <span
                                                    v-if="countdown !== null"
                                                    class="ml-1 opacity-75"
                                                    >({{ countdown }}s)</span
                                                >
                                            </PrimaryButton>
                                        </div>
                                    </template>

                                    <!-- Error State -->
                                    <template
                                        v-else-if="scanStatus === 'error'"
                                    >
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-full bg-red-500/20 text-red-400 ring-2 ring-red-500/50"
                                        >
                                            <AlertCircle :size="32" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-red-300"
                                            >
                                                {{ statusMessage }}
                                            </p>
                                            <p
                                                v-if="lastScannedCode"
                                                class="mt-1 font-mono text-xs text-neutral-400"
                                            >
                                                Código: {{ lastScannedCode }}
                                            </p>
                                        </div>
                                        <PrimaryButton
                                            type="button"
                                            color="pill"
                                            padding="px-4 py-1.5"
                                            class="mt-2"
                                            @click="resetScanner"
                                        >
                                            <RotateCcw :size="14" />
                                            <span>Tentar Novamente</span>
                                        </PrimaryButton>
                                    </template>
                                </div>
                            </transition>
                        </QrcodeStream>
                    </div>

                    <!-- Live Camera Controls Toolbar -->
                    <div
                        v-if="isCameraReady"
                        class="mt-4 flex items-center justify-center gap-2"
                    >
                        <!-- Flip Camera -->
                        <button
                            type="button"
                            class="pill-container cursor-pointer gap-1.5 px-3 py-1.5 text-xs font-medium text-neutral-300 transition-all hover:border-white/20 hover:text-white active:scale-95"
                            title="Alternar câmara frontal/traseira"
                            @click="toggleCameraFacing"
                        >
                            <SwitchCamera :size="14" />
                            <span class="hidden sm:inline">Virar</span>
                        </button>

                        <!-- Flashlight Toggle (if supported) -->
                        <button
                            v-if="hasTorch"
                            type="button"
                            class="pill-container cursor-pointer gap-1.5 px-3 py-1.5 text-xs font-medium transition-all active:scale-95"
                            :class="
                                torchActive
                                    ? 'border-amber-400/50 bg-amber-500/20 text-amber-200'
                                    : 'text-neutral-300 hover:border-white/20 hover:text-white'
                            "
                            title="Ligar/desligar lanterna"
                            @click="toggleTorch"
                        >
                            <Zap v-if="torchActive" :size="14" />
                            <ZapOff v-else :size="14" />
                            <span class="hidden sm:inline">Lanterna</span>
                        </button>

                        <!-- Pause / Resume -->
                        <button
                            type="button"
                            class="pill-container cursor-pointer gap-1.5 px-3 py-1.5 text-xs font-medium text-neutral-300 transition-all hover:border-white/20 hover:text-white active:scale-95"
                            :title="isPaused ? 'Retomar câmara' : 'Pausar câmara'"
                            @click="togglePause"
                        >
                            <Play v-if="isPaused" :size="14" />
                            <Pause v-else :size="14" />
                            <span class="hidden sm:inline">{{
                                isPaused ? "Retomar" : "Pausar"
                            }}</span>
                        </button>
                    </div>

                    <!-- Continuous Mode Toggle -->
                    <div class="mt-4 flex items-center justify-center">
                        <label
                            class="flex cursor-pointer items-center gap-2 text-xs text-neutral-400 select-none hover:text-neutral-300"
                        >
                            <input
                                v-model="autoContinue"
                                type="checkbox"
                                class="h-3.5 w-3.5 rounded border-white/20 bg-black/40 text-sinf-primary focus:ring-0 focus:ring-offset-0"
                            />
                            <span>Avançar automaticamente após validação</span>
                        </label>
                    </div>
                </div>

                <div v-show="activeMode === 'manual'" class="w-full">
                    <form class="space-y-4" @submit.prevent="onManualSubmit">
                        <div>
                            <TextInput
                                id="manual-code"
                                v-model="form.quest_code"
                                label="Código do Participante"
                                placeholder="a1b2c3d4e5"
                                type="text"
                                required
                                maxlength="10"
                                :error-message="form.errors.quest_code"
                                class="w-full text-center font-mono tracking-widest uppercase"
                            >
                            </TextInput>
                        </div>

                        <div
                            v-if="
                                scanStatus === 'success'
                            "
                            class="rounded-2xl p-3.5 text-xs sm:text-sm border border-emerald-500/30 bg-emerald-500/10 text-emerald-300"
                        >
                            <div class="flex items-center gap-2 font-medium">
                                <CheckCircle2
                                    v-if="scanStatus === 'success'"
                                    :size="16"
                                    class="shrink-0 text-emerald-400"
                                />
                                <span>{{ statusMessage }}</span>
                            </div>
                        </div>

                        <PrimaryButton
                            type="submit"
                            color="pill"
                            full-width
                            :disabled="
                                form.processing ||
                                !form.quest_code ||
                                !form.quest
                            "
                        >
                            <span v-if="form.processing"
                                >A processar validação...</span
                            >
                            <span v-else>Confirmar Tarefa</span>
                        </PrimaryButton>
                    </form>
                </div>
            </Card>

            <Modal
                v-model="showInstructionsModal"
                title="Como Validar Tarefas?"
                description="Segue estes passos rápidos para registar os participantes:"
                max-width="md"
            >
                <div class="space-y-4 py-2">
                    <div
                        class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-3.5"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sinf-primary/30 text-xs font-bold text-white"
                        >
                            1
                        </div>
                        <div class="text-xs sm:text-sm">
                            <p class="font-medium text-white">
                                Pedir ao participante para abrir o Perfil
                            </p>
                            <p class="mt-0.5 text-neutral-400">
                                No telemóvel do participante, aceder a Perfil e
                                tocar no botão
                                <strong class="text-white">QR Code</strong>.
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-3.5"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sinf-primary/30 text-xs font-bold text-white"
                        >
                            2
                        </div>
                        <div class="text-xs sm:text-sm">
                            <p class="font-medium text-white">
                                Apontar a câmara ou introduzir o código
                            </p>
                            <p class="mt-0.5 text-neutral-400">
                                Alinha o código no visor da câmara. Se preferir,
                                introduz o código alfanumérico de 10 caracteres
                                no separador
                                <strong class="text-white">Manual</strong>.
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-3.5"
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sinf-primary/30 text-xs font-bold text-white"
                        >
                            3
                        </div>
                        <div class="text-xs sm:text-sm">
                            <p class="font-medium text-white">
                                Confirmar a Tarefa Ativa
                            </p>
                            <p class="mt-0.5 text-neutral-400">
                                Garante que o evento ou banca correto está
                                selecionado no seletor de tarefa antes da
                                leitura.
                            </p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <PrimaryButton
                            type="button"
                            color="pill"
                            full-width
                            @click="showInstructionsModal = false"
                        >
                            Entendido
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes scan-laser {
    0% {
        top: 6%;
        opacity: 0.3;
    }
    50% {
        top: 92%;
        opacity: 1;
    }
    100% {
        top: 6%;
        opacity: 0.3;
    }
}

.laser-line {
    animation: scan-laser 2.4s ease-in-out infinite;
}
</style>
