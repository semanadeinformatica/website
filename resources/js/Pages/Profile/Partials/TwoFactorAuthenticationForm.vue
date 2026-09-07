<script setup lang="ts">
import { ref, computed, watch, onMounted } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Card from "@/Components/UI/Card.vue";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmsPassword from "@/Components/ConfirmsPassword.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CodeInput from "@/Components/CodeInput.vue";
import { Copy } from "@lucide/vue";
import { route } from "ziggy-js";
import axios from "axios";

interface Props {
    requiresConfirmation: boolean;
}

const props = defineProps<Props>();

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const setupModalOpen = ref(false);
const recoveryCodesModalOpen = ref(false);
const qrCode = ref<string | null>(null);
const setupKey = ref<string | null>(null);
const recoveryCodes = ref<string[]>([]);
const copiedKey = ref(false);

const confirmationForm = useForm({
    code: "",
});

const isTwoFactorEnabled = computed(() => {
    const user = usePage().props.auth.user;
    if (!user?.two_factor_enabled) return false;
    if (confirming.value) return false;
    if (props.requiresConfirmation) {
        return Boolean(user?.two_factor_confirmed_at);
    }
    return true;
});

onMounted(() => {
    const user = usePage().props.auth.user;
    if (
        props.requiresConfirmation &&
        user?.two_factor_enabled &&
        !user?.two_factor_confirmed_at
    ) {
        disableTwoFactorAuthentication();
    }
});

watch(isTwoFactorEnabled, (enabled) => {
    if (!enabled) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
        confirming.value = false;
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;
    confirming.value = props.requiresConfirmation;

    router.post(
        route("two-factor.enable"),
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                Promise.all([
                    showQrCode(),
                    showSetupKey(),
                    showRecoveryCodes(),
                ]).then(() => {
                    setupModalOpen.value = true;
                }),
            onFinish: () => {
                enabling.value = false;
            },
        },
    );
};

const showQrCode = async () => {
    const response = await axios.get(route("two-factor.qr-code"));
    qrCode.value = response.data.svg;
};

const showSetupKey = async () => {
    const response = await axios.get(route("two-factor.secret-key"));
    setupKey.value = response.data.secretKey;
};

const showRecoveryCodes = async () => {
    const response = await axios.get(route("two-factor.recovery-codes"));
    recoveryCodes.value = response.data;
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route("two-factor.confirm"), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            setupModalOpen.value = false;
            qrCode.value = null;
            setupKey.value = null;
            showRecoveryCodes().then(() => {
                recoveryCodesModalOpen.value = true;
            });
        },
    });
};

const openRecoveryCodesModal = () => {
    showRecoveryCodes().then(() => {
        recoveryCodesModalOpen.value = true;
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route("two-factor.recovery-codes"))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route("two-factor.disable"), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
            setupModalOpen.value = false;
            recoveryCodesModalOpen.value = false;
        },
        onError: () => {
            disabling.value = false;
        },
    });
};

const cancelSetup = () => {
    setupModalOpen.value = false;
    disableTwoFactorAuthentication();
};

const copySetupKey = () => {
    if (!setupKey.value) return;
    navigator.clipboard.writeText(setupKey.value);
    copiedKey.value = true;
    setTimeout(() => {
        copiedKey.value = false;
    }, 2000);
};
</script>

<template>
    <Card as="section" :interactive="false" padding="p-6 sm:p-8">
        <template #header>
            <div
                class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
            >
                <div class="space-y-1">
                    <h2
                        class="text-lg font-bold tracking-tight text-white sm:text-xl"
                    >
                        Autenticação de Dois Fatores (2FA)
                    </h2>
                    <p class="text-xs text-neutral-400 sm:text-sm">
                        Adiciona uma camada extra de segurança ao iniciar sessão
                        através de um código temporário.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-3 text-sm text-neutral-300">
            <p v-if="isTwoFactorEnabled">
                A tua conta está protegida com autenticação de dois fatores.
                Ser-te-á solicitado um código de 6 dígitos gerado pela tua
                aplicação de autenticação (como Google Authenticator, 1Password
                ou Authy) sempre que iniciares sessão.
            </p>
            <p v-else>
                Com o 2FA ativado, ser-te-á pedido um código de 6 dígitos gerado
                pela tua aplicação de autenticação (como Google Authenticator,
                1Password ou Authy) sempre que iniciares sessão.
            </p>
        </div>

        <template #footer>
            <div class="flex w-full flex-wrap items-center justify-end gap-3">
                <!-- Turned Off (Desativada) -->
                <div v-if="!isTwoFactorEnabled">
                    <ConfirmsPassword
                        title="Confirmar Palavra-passe"
                        content="Para ativar a autenticação de dois fatores, confirma a tua palavra-passe."
                        button="Continuar"
                        @confirmed="enableTwoFactorAuthentication"
                    >
                        <PrimaryButton
                            type="button"
                            :class="{ 'opacity-25': enabling }"
                            :disabled="enabling"
                        >
                            {{ enabling ? "A iniciar..." : "Ativar 2FA" }}
                        </PrimaryButton>
                    </ConfirmsPassword>
                </div>

                <!-- Turned On (Ativada) -->
                <div v-else class="flex flex-wrap items-center gap-3">
                    <ConfirmsPassword
                        title="Ver Códigos de Recuperação"
                        content="Confirma a tua palavra-passe para visualizar os teus códigos de emergência."
                        button="Mostrar"
                        @confirmed="openRecoveryCodesModal"
                    >
                        <PrimaryButton type="button" color="pill">
                            Ver códigos de recuperação
                        </PrimaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword
                        title="Desativar 2FA"
                        content="Tens a certeza que pretendes desativar o 2FA? A segurança da tua conta será reduzida."
                        button="Desativar"
                        @confirmed="disableTwoFactorAuthentication"
                    >
                        <PrimaryButton
                            type="button"
                            color="danger"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                        >
                            {{ disabling ? "A desativar..." : "Desativar 2FA" }}
                        </PrimaryButton>
                    </ConfirmsPassword>
                </div>
            </div>
        </template>
    </Card>

    <Modal
        v-model="setupModalOpen"
        max-width="md"
        title="Configurar Autenticação de Dois Fatores"
        description="Digitaliza o código QR na tua aplicação de autenticação para associar a conta."
        :closeable="false"
        :show-close-button="false"
    >
        <div class="flex flex-col items-center gap-5 py-2">
            <div
                v-if="qrCode"
                class="flex items-center justify-center rounded-2xl bg-white p-4"
                v-html="qrCode"
            />

            <div v-if="setupKey" class="w-full space-y-1.5 text-center">
                <span class="text-xs font-medium text-neutral-400"
                    >Chave de configuração manual:</span
                >
                <div
                    class="group flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/10 bg-black/50 px-3 py-2 font-mono text-xs text-white transition-colors hover:border-white/20"
                    title="Clique para copiar"
                    @click="copySetupKey"
                >
                    <span class="truncate">{{ setupKey }}</span>
                    <Copy
                        :size="13"
                        class="shrink-0 text-neutral-400 group-hover:text-white"
                    />
                </div>
                <span
                    v-if="copiedKey"
                    class="text-[11px] font-medium text-emerald-400"
                    >Copiado para a área de transferência!</span
                >
            </div>

            <div class="w-full max-w-xs space-y-2 pt-1 text-center">
                <label
                    for="two_factor_code"
                    class="text-xs font-semibold text-white"
                >
                    Código de 6 dígitos gerado pela app:
                </label>
                <CodeInput
                    id="two_factor_code"
                    v-model="confirmationForm.code"
                    label="Código"
                    :center="true"
                    :error-message="confirmationForm.errors.code"
                    @complete="confirmTwoFactorAuthentication"
                />
            </div>
        </div>

        <template #footer>
            <div class="flex w-full items-center justify-between gap-3">
                <PrimaryButton
                    type="button"
                    color="pill"
                    :disabled="confirmationForm.processing"
                    @click="cancelSetup"
                >
                    Cancelar
                </PrimaryButton>

                <PrimaryButton
                    type="button"
                    :class="{ 'opacity-25': confirmationForm.processing }"
                    :disabled="confirmationForm.processing"
                    @click="confirmTwoFactorAuthentication"
                >
                    {{
                        confirmationForm.processing
                            ? "A verificar..."
                            : "Concluir ativação"
                    }}
                </PrimaryButton>
            </div>
        </template>
    </Modal>

    <Modal
        v-model="recoveryCodesModalOpen"
        max-width="md"
        title="Códigos de Recuperação"
        description="Armazena estes códigos num local seguro. Cada código pode ser usado uma única vez se perderes o acesso ao dispositivo 2FA."
    >
        <div class="space-y-4 py-2">
            <div
                class="grid grid-cols-2 gap-2.5 rounded-2xl border border-white/10 bg-black/50 p-4 font-mono text-xs text-neutral-200 sm:text-sm"
            >
                <div
                    v-for="code in recoveryCodes"
                    :key="code"
                    class="py-1 text-center select-all"
                >
                    {{ code }}
                </div>
            </div>

            <p class="text-xs text-neutral-400">
                Dica: Podes copiar e guardar estes códigos no teu gestor de
                palavras-passe ou imprimi-los para um local seguro.
            </p>
        </div>

        <template #footer>
            <div class="flex w-full items-center justify-between gap-3">
                <ConfirmsPassword
                    title="Regenerar Códigos"
                    content="Tens a certeza que desejas regenerar os teus códigos de recuperação? Todos os anteriores serão invalidados."
                    button="Regenerar"
                    @confirmed="regenerateRecoveryCodes"
                >
                    <PrimaryButton type="button" color="pill">
                        Regenerar novos códigos
                    </PrimaryButton>
                </ConfirmsPassword>

                <PrimaryButton
                    type="button"
                    @click="recoveryCodesModalOpen = false"
                >
                    Concluído
                </PrimaryButton>
            </div>
        </template>
    </Modal>
</template>
