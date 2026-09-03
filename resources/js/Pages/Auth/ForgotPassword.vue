<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
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
    <CardLayout
        title="Repor password"
        heading="Recuperar password"
        subtitle="Indica o teu email e enviaremos um link para recuperares o acesso"
    >
        <div
            v-if="status"
            class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-center text-xs font-medium text-emerald-400"
        >
            {{ status }}
        </div>

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <TextInput
                id="email"
                v-model="form.email"
                label="Email"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="nome@exemplo.pt"
                :error-message="form.errors.email"
            />

            <PrimaryButton :disabled="form.processing" class="mt-1 w-full">
                <span v-if="form.processing">A enviar link...</span>
                <span v-else>Enviar email</span>
            </PrimaryButton>

            <div class="pt-4 text-center text-xs text-neutral-400 sm:text-sm">
                Lembraste-te da password?
                <Link
                    :href="route('login')"
                    class="ml-1 font-semibold text-white underline underline-offset-4 transition-colors hover:text-neutral-200"
                >
                    Voltar ao início de sessão
                </Link>
            </div>
        </form>
    </CardLayout>
</template>
