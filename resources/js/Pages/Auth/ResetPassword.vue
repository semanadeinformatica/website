<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import { route } from "ziggy-js";

interface Props {
    email: string;
    token: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.update"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <CardLayout
        title="Repor password"
        heading="Repor a tua password"
        subtitle="Escolhe uma nova password segura para a tua conta"
    >
        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <TextInput
                id="email"
                v-model="form.email"
                label="Email"
                type="email"
                required
                autofocus
                autocomplete="username"
                :error-message="form.errors.email"
            />

            <TextInput
                id="password"
                v-model="form.password"
                label="Password"
                type="password"
                required
                autocomplete="new-password"
                :error-message="form.errors.password"
            />

            <TextInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                label="Confirmar password"
                type="password"
                required
                autocomplete="new-password"
                :error-message="form.errors.password_confirmation"
            />

            <PrimaryButton :disabled="form.processing" class="mt-2 w-full">
                <span v-if="form.processing">A repor...</span>
                <span v-else>Repor password</span>
            </PrimaryButton>

            <div
                class="mt-2 border-t border-white/8 pt-4 text-center text-xs text-neutral-400 sm:text-sm"
            >
                <Link
                    :href="route('login')"
                    class="font-semibold text-white underline underline-offset-4 transition-colors hover:text-neutral-200"
                >
                    Voltar ao início de sessão
                </Link>
            </div>
        </form>
    </CardLayout>
</template>
