<script setup lang="ts">
import { nextTick, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { route } from "ziggy-js";
import CardLayout from "@/Layouts/CardLayout.vue";

const recovery = ref(false);

const form = useForm({
    code: "",
    recovery_code: "",
});

const recoveryCodeInput = ref<HTMLInputElement | null>(null);
const codeInput = ref<HTMLInputElement | null>(null);

const toggleRecovery = async () => {
    recovery.value = !recovery.value;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value?.focus();
        form.code = "";
    } else {
        codeInput.value?.focus();
        form.recovery_code = "";
    }
};

const submit = () => {
    form.post(route("two-factor.login"));
};
</script>

<template>
    <CardLayout
        title="Confirmar Two-Factor"
        heading="Autenticação de Dois Fatores"
        :subtitle="
            !recovery
                ? 'Confirma o acesso inserindo o código da tua aplicação de autenticação.'
                : 'Confirma o acesso inserindo um dos teus códigos de recuperação de emergência.'
        "
    >
        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <TextInput
                v-if="!recovery"
                id="code"
                ref="codeInput"
                v-model="form.code"
                label="Código"
                type="text"
                inputmode="numeric"
                autofocus
                autocomplete="one-time-code"
                :error-message="form.errors.code"
            />

            <TextInput
                v-else
                id="recovery_code"
                ref="recoveryCodeInput"
                v-model="form.recovery_code"
                label="Código de recuperação"
                type="text"
                autofocus
                autocomplete="one-time-code"
                :error-message="form.errors.recovery_code"
            />

            <PrimaryButton :disabled="form.processing" class="mt-1 w-full">
                <span v-if="form.processing">A verificar...</span>
                <span v-else>Entrar</span>
            </PrimaryButton>

            <div
                class="mt-2 border-t border-white/8 pt-4 text-center text-xs text-neutral-400 sm:text-sm"
            >
                <button
                    type="button"
                    class="cursor-pointer font-medium text-white underline underline-offset-4 transition-colors hover:text-neutral-200"
                    @click.prevent="toggleRecovery"
                >
                    <template v-if="!recovery">
                        Utilizar código de recuperação de emergência
                    </template>
                    <template v-else>
                        Utilizar código da aplicação autenticadora
                    </template>
                </button>
            </div>
        </form>
    </CardLayout>
</template>
