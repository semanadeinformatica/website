<script setup lang="ts">
import { computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { MailCheck } from "@lucide/vue";
import { route } from "ziggy-js";

interface Props {
    status: string;
}

const props = defineProps<Props>();

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent",
);
</script>

<template>
    <AuthLayout
        title="Verificar email"
        heading="Verifica o teu email"
        subtitle="Enviámos uma ligação de confirmação para o teu endereço de email. Clica nela para concluir o registo e ativar a tua conta."
        :icon="MailCheck"
    >
        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-center text-xs font-medium text-emerald-400"
        >
            Um novo link de verificação foi enviado para o endereço de email
            associado à tua conta.
        </div>

        <form method="POST" class="flex flex-col gap-5" @submit.prevent="submit">
            <div class="flex justify-center w-full">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">A reenviar...</span>
                    <span v-else>Reenviar email de verificação</span>
                </PrimaryButton>
            </div>

            <div
                class="mt-2 flex items-center justify-between pt-2 text-xs sm:text-sm"
            >
                <Link
                    :href="route('profile.show')"
                    class="text-neutral-400 underline underline-offset-2 transition-colors hover:text-white"
                >
                    Editar Perfil
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="cursor-pointer text-red-400 underline underline-offset-2 transition-colors hover:text-red-300"
                >
                    Terminar Sessão
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
