<script setup lang="ts">
import { computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { route } from "ziggy-js";
import CardLayout from "@/Layouts/CardLayout.vue";

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
    <CardLayout
        title="Verificar email"
        heading="Verifica o teu email"
        subtitle="Antes de continuar, clica no link de confirmação que enviámos para o teu email. Se não o recebeste, podemos enviar outro."
    >
        <div
            v-if="verificationLinkSent"
            class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-center text-xs font-medium text-emerald-400"
        >
            Um novo link de verificação foi enviado para o endereço de email
            associado à tua conta.
        </div>

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <PrimaryButton :disabled="form.processing" class="w-full">
                <span v-if="form.processing">A reenviar...</span>
                <span v-else>Reenviar email de verificação</span>
            </PrimaryButton>

            <div
                class="mt-2 flex items-center justify-between border-t border-white/8 pt-4 text-xs sm:text-sm"
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
    </CardLayout>
</template>
