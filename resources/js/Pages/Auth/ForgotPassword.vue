<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { KeyRound } from "@lucide/vue";
import { route } from "ziggy-js";

interface Props {
    status?: string;
}

defineProps<Props>();

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <AuthLayout
        title="Repor password"
        heading="Recuperar password"
        subtitle="Indica o teu endereço de email associado à conta e enviaremos uma ligação segura para restabeleceres o acesso."
        :icon="KeyRound"
    >
        <div
            v-if="status"
            class="mb-4 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-center text-xs font-medium text-emerald-400"
        >
            {{ status }}
        </div>

        <form
            method="POST"
            class="flex flex-col gap-5"
            @submit.prevent="submit"
        >
            <TextInput
                id="email"
                v-model="form.email"
                name="email"
                label="Email"
                type="email"
                required
                autofocus
                autocomplete="username email"
                placeholder="nome@exemplo.pt"
                :error-message="form.errors.email"
            />

            <div class="mt-2 flex w-full justify-center">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">A enviar link...</span>
                    <span v-else>Enviar email</span>
                </PrimaryButton>
            </div>

            <div class="pt-2 text-center text-xs text-neutral-400 sm:text-sm">
                Lembraste-te da password?
                <Link
                    :href="route('login')"
                    class="ml-1 text-neutral-300 transition-colors hover:text-white"
                >
                    Voltar ao início de sessão
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
