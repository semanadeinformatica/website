<script setup lang="ts">
import { type User, isParticipant } from "@/Types/User";
import { useForm, usePage } from "@inertiajs/vue3";
import {
    ExternalLink,
    FileText,
    Upload,
    ChevronDown,
    Trash2,
    Loader2,
} from "@lucide/vue";
import { ref, computed } from "vue";
import { route } from "ziggy-js";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface Props {
    item: User | undefined;
}

const props = defineProps<Props>();

const page = usePage();

const isOwnProfile = computed(() =>
    Boolean(
        page.props.auth.user?.id && page.props.auth.user.id === props.item?.id,
    ),
);

const participant = computed(() =>
    props.item && isParticipant(props.item) ? props.item.usertype : undefined,
);

const hasCV = computed(() =>
    Boolean(participant.value?.cv_path || participant.value?.cv_url),
);

const cvUrl = computed(() => {
    if (participant.value?.cv_url) return participant.value.cv_url;
    if (participant.value?.cv_path) return `/storage/${participant.value.cv_path}`;
    return undefined;
});

const previewOpen = ref(false);
const isUploading = ref(false);
const isDeleting = ref(false);

const form = useForm({
    _method: "PUT",
    name: props.item?.name,
    email: props.item?.email,
    cv: null as File | null,
});

const cvInput = ref<HTMLInputElement | null>(null);

const selectCV = () => {
    cvInput.value?.click();
};

const togglePreview = () => {
    previewOpen.value = !previewOpen.value;
};

const uploadCV = () => {
    const cv = cvInput.value?.files?.[0];
    if (cv) {
        isUploading.value = true;
        form.cv = cv;
        form.post(route("current-user-cv.update"), {
            preserveScroll: true,
            onSuccess: () => {
                clearCVFileInput();
                previewOpen.value = true;
            },
            onFinish: () => {
                isUploading.value = false;
            },
        });
    }
};

const deleteCV = () => {
    if (!confirm("Tens a certeza de que pretendes remover o teu currículo?")) {
        return;
    }
    isDeleting.value = true;
    form.delete(route("current-user-cv.destroy"), {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const clearCVFileInput = () => {
    if (cvInput.value?.value) {
        cvInput.value.value = "";
    }
};
</script>

<template>
    <div
        class="flex w-full flex-col rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md"
    >
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-neutral-300"
                >
                    <FileText :size="20" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-white sm:text-lg">
                            Currículo Vitae (CV)
                        </h2>
                    </div>
                    <p class="text-xs text-neutral-400">
                        {{
                            hasCV
                                ? "O teu CV está pronto para partilha com parceiros"
                                : "Ainda não submeteste o teu CV em formato PDF"
                        }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <template v-if="isOwnProfile">
                    <input
                        ref="cvInput"
                        type="file"
                        class="hidden"
                        accept="application/pdf"
                        @change="uploadCV"
                    />
                    <PrimaryButton
                        v-if="hasCV"
                        type="button"
                        color="pill"
                        padding="p-2 sm:p-2.5"
                        :disabled="isDeleting || isUploading"
                        title="Remover currículo"
                        aria-label="Remover currículo"
                        @click="deleteCV"
                    >
                        <Loader2
                            v-if="isDeleting"
                            :size="16"
                            class="animate-spin text-rose-400"
                        />
                        <Trash2 v-else :size="16" class="text-rose-400" />
                    </PrimaryButton>

                    <PrimaryButton
                        v-if="hasCV"
                        type="button"
                        color="pill"
                        padding="p-2 sm:p-2.5"
                        :disabled="isUploading || isDeleting"
                        title="Substituir CV"
                        aria-label="Substituir CV"
                        @click.prevent="selectCV"
                    >
                        <Loader2
                            v-if="isUploading"
                            :size="16"
                            class="animate-spin"
                        />
                        <Upload v-else :size="16" />
                    </PrimaryButton>

                    <PrimaryButton
                        v-else
                        type="button"
                        color="pill"
                        padding="px-4 py-2"
                        :disabled="isUploading || isDeleting"
                        title="Carregar CV"
                        aria-label="Carregar CV"
                        @click.prevent="selectCV"
                    >
                        <Loader2
                            v-if="isUploading"
                            :size="15"
                            class="animate-spin"
                        />
                        <Upload v-else :size="15" />
                        <span>{{
                            isUploading ? "A enviar..." : "Carregar CV"
                        }}</span>
                    </PrimaryButton>
                </template>

                <template v-if="hasCV && cvUrl">
                    <PrimaryButton
                        :href="cvUrl"
                        external
                        target="_blank"
                        rel="noopener noreferrer"
                        color="pill"
                        padding="px-4 py-2"
                    >
                        <ExternalLink :size="15" />
                        <span>Abrir PDF</span>
                    </PrimaryButton>

                    <PrimaryButton
                        type="button"
                        color="pill"
                        padding="px-4 py-2"
                        @click="togglePreview"
                    >
                        <span>Pré-visualização</span>
                        <ChevronDown
                            :size="15"
                            class="transition-transform duration-200"
                            :class="{ 'rotate-180': previewOpen }"
                        />
                    </PrimaryButton>
                </template>
            </div>
        </div>

        <div
            v-if="previewOpen && hasCV && cvUrl"
            class="mt-6 flex flex-col gap-3 rounded-2xl border border-white/10 bg-neutral-950/80 p-3 sm:p-5"
        >
            <div
                class="relative w-full overflow-hidden rounded-xl border border-white/10 bg-neutral-900 shadow-inner"
            >
                <iframe
                    :src="cvUrl"
                    class="hidden h-162.5 w-full bg-white sm:block"
                    title="Pré-visualização do CV"
                />

                <div
                    class="flex flex-col items-center justify-center gap-3 py-10 text-center sm:hidden"
                >
                    <FileText :size="36" class="text-neutral-400" />
                    <p class="px-4 text-xs text-neutral-300">
                        Abre o teu documento PDF para consulta detalhada no
                        navegador.
                    </p>
                    <PrimaryButton
                        :href="cvUrl"
                        external
                        target="_blank"
                        rel="noopener noreferrer"
                        color="pill"
                        padding="px-4 py-2"
                    >
                        <ExternalLink :size="14" />
                        <span>Ver CV no navegador</span>
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <div
            v-if="!hasCV && isOwnProfile"
            class="mt-5 flex items-start gap-3.5 rounded-2xl border border-white/8 bg-white/4 p-4 text-xs text-neutral-300 sm:text-sm"
        >
            <div class="space-y-1">
                <p class="font-semibold text-white">
                    Porque deves carregar o teu CV?
                </p>
                <p class="leading-relaxed text-neutral-400">
                    Esta é uma excelente oportunidade para te dares a conhecer
                    às empresas parceiras da SINF! No fim do evento, o teu CV
                    será partilhado com as bancas que visitares, facilitando o
                    contacto e futuras oportunidades de recrutamento.
                </p>
            </div>
        </div>
    </div>
</template>
