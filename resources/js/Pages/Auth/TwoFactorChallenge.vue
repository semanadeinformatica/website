<script setup lang="ts">
import { nextTick, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import CodeInput from "@/Components/Form/CodeInput.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { Smartphone, KeyRound } from "@lucide/vue";
import { route } from "ziggy-js";

const recovery = ref(false);

const form = useForm({
    code: "",
    recovery_code: "",
});

const recoveryCodeInput = ref<HTMLInputElement | null>(null);
const codeInput = ref<InstanceType<typeof CodeInput> | null>(null);

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
    <AuthLayout
        title="Confirmar Two-Factor"
        heading="Dois Fatores"
        :subtitle="
            !recovery
                ? 'Insere o código de 6 dígitos gerado pela tua aplicação autenticadora para verificar a tua identidade.'
                : 'Insere um dos teus códigos de recuperação de emergência gerados ao configurar a autenticação.'
        "
        :icon="!recovery ? Smartphone : KeyRound"
    >
        <form
            method="POST"
            class="flex flex-col gap-6"
            @submit.prevent="submit"
        >
            <CodeInput
                v-if="!recovery"
                id="code"
                ref="codeInput"
                v-model="form.code"
                name="code"
                :error-message="form.errors.code"
                autofocus
                @complete="submit"
            />

            <TextInput
                v-else
                id="recovery_code"
                ref="recoveryCodeInput"
                v-model="form.recovery_code"
                name="recovery_code"
                label="Código de recuperação"
                type="text"
                autofocus
                autocomplete="one-time-code"
                placeholder="abcde-12345"
                :error-message="form.errors.recovery_code"
            />

            <div v-if="recovery" class="flex w-full justify-center">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">A verificar...</span>
                    <span v-else>Entrar</span>
                </PrimaryButton>
            </div>

            <div class="text-center">
                <button
                    type="button"
                    class="cursor-pointer text-xs text-neutral-400 transition-colors hover:text-neutral-200 focus:outline-none"
                    @click.prevent="toggleRecovery"
                >
                    <template v-if="!recovery">
                        Utilizar código de recuperação
                    </template>
                    <template v-else>
                        Utilizar código da aplicação autenticadora
                    </template>
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
