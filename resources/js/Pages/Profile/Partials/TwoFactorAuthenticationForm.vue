<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import ActionSection from "@/Components/ActionSection.vue";
import ConfirmsPassword from "@/Components/ConfirmsPassword.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import route from "ziggy-js";
import axios from "axios";

interface Props {
    requiresConfirmation: boolean;
}

const props = defineProps<Props>();

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref<string | null>(null);
const setupKey = ref<string | null>(null);
const recoveryCodes = ref<string[]>([]);

const confirmationForm = useForm({
    code: "",
});

const twoFactorEnabled = computed(
    () => !enabling.value && usePage().props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

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
                ]),
            onFinish: () => {
                enabling.value = false;
                confirming.value = props.requiresConfirmation;
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
            qrCode.value = null;
            setupKey.value = null;
        },
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
        },
    });
};
</script>

<template>
    <ActionSection>
        <template #title> <span class="text-text-color">Autenticação de 2 fatores </span></template>

        <template #description>
            <span class="text-text-color">
            Adiciona segurança à tua conta usando autenticação de
            dois fatores.
            </span>
        </template>

        <template #content>
            <h3
                v-if="twoFactorEnabled && !confirming"
                class="text-lg font-medium text-text-color"
            >
                Ativaste a autenticação de dois fatores.
            </h3>

            <h3
                v-else-if="twoFactorEnabled && confirming"
                class="text-lg font-medium text-text-color"
            >
                Conclui a autenticação de dois fatores.
            </h3>

            <h3 v-else class="text-lg font-medium text-text-color">
                Não ativaste a autenticação de dois fatores.
            </h3>

            <div class="mt-3 max-w-xl text-sm text-text-color">
                <p>
                    Quando a autenticação de dois fatores for ativada, ser-te-á
                    solicitado um token aleatório e seguro durante a
                    autenticação. Podes recuperar este token através da
                    aplicação do Google Authenticator.
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 max-w-xl text-sm text-text-color">
                        <p v-if="confirming" class="font-semibold">
                            Para terminar de ativar a autenticação de dois
                            fatores, digitaliza o código QR a seguir usando a
                            app de Authenticator do teu telemóvel ou insere a
                            tecla de configuração e fornece o código OTP gerado.
                        </p>

                        <p v-else>
                            A autenticação de dois fatores está agora ativada.
                            Dá scan ao código QR usando a aplicação de
                            Authenticator do teu telemóvel ou insere a chave de
                            configuração.
                        </p>
                    </div>

                    <div
                        class="mt-4 inline-block bg-2025-blue p-2"
                        v-html="qrCode"
                    />

                    <div
                        v-if="setupKey"
                        class="mt-4 max-w-xl text-sm text-text-color"
                    >
                        <p class="font-semibold">
                            Setup Key: <span v-html="setupKey"></span>
                        </p>
                    </div>

                    <TextInput
                        v-if="confirming"
                        id="code"
                        v-model="confirmationForm.code"
                        type="text"
                        name="code"
                        label="Code"
                        inputmode="numeric"
                        autofocus
                        autocomplete="one-time-code"
                        :error-message="confirmationForm.errors.code"
                        @keyup.enter="confirmTwoFactorAuthentication"
                    />
                </div>

                <div v-if="recoveryCodes.length > 0 && !confirming">
                    <div class="mt-4 max-w-xl text-sm text-text-color">
                        <p class="font-semibold">
                            Armazena estes códigos de recuperação numa gestor de
                            senhas seguro. Eles podem ser usados para recuperar
                            o acesso à tua conta se o teu dispositivo de
                            autenticação de dois fatores for perdido.
                        </p>
                    </div>

                    <div
                        class="mt-4 grid max-w-xl gap-1 rounded-lg bg-gray-100 px-4 py-4 font-mono text-sm"
                    >
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div v-if="!twoFactorEnabled">
                    <ConfirmsPassword
                        @confirmed="enableTwoFactorAuthentication"
                    >
                        <PrimaryButton
                            type="button"
                            :class="{ 'opacity-25': enabling }"
                            :disabled="enabling"
                        >
                            Ativar
                        </PrimaryButton>
                    </ConfirmsPassword>
                </div>

                <div v-else>
                    <ConfirmsPassword
                        @confirmed="confirmTwoFactorAuthentication"
                    >
                        <PrimaryButton
                            v-if="confirming"
                            type="button"
                            class="mr-3"
                            :class="{ 'opacity-25': enabling }"
                            :disabled="enabling"
                        >
                            Confirm
                        </PrimaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
                        <PrimaryButton
                            v-if="recoveryCodes.length > 0 && !confirming"
                            class="mr-3"
                        >
                            Regenerate Recovery Codes
                        </PrimaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="showRecoveryCodes">
                        <PrimaryButton
                            v-if="recoveryCodes.length === 0 && !confirming"
                            class="mr-3"
                        >
                            Show Recovery Codes
                        </PrimaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword
                        @confirmed="disableTwoFactorAuthentication"
                    >
                        <PrimaryButton
                            v-if="confirming"
                            :class="{ 'opacity-25': disabling }"
                            color="red"
                            :disabled="disabling"
                        >
                            Cancel
                        </PrimaryButton>
                    </ConfirmsPassword>

                    <ConfirmsPassword
                        @confirmed="disableTwoFactorAuthentication"
                    >
                        <PrimaryButton
                            v-if="!confirming"
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                            color="red"
                        >
                            Disable
                        </PrimaryButton>
                    </ConfirmsPassword>
                </div>
            </div>
        </template>
    </ActionSection>
</template>
