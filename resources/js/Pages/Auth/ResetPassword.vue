<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { KeyRound } from "@lucide/vue";
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
    <AuthLayout
        title="Repor password"
        heading="Repor password"
        subtitle="Escolhe uma nova password segura para garantir a proteção e acesso à tua conta."
        :icon="KeyRound"
    >
        <form method="POST" class="flex flex-col gap-5" @submit.prevent="submit">
            <TextInput
                id="email"
                v-model="form.email"
                name="email"
                label="Email"
                type="email"
                required
                autofocus
                autocomplete="username email"
                :error-message="form.errors.email"
            />

            <TextInput
                id="password"
                v-model="form.password"
                name="password"
                label="Password"
                type="password"
                required
                autocomplete="new-password"
                :error-message="form.errors.password"
            />

            <TextInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                name="password_confirmation"
                label="Confirmar password"
                type="password"
                required
                autocomplete="new-password"
                :error-message="form.errors.password_confirmation"
            />

            <div class="mt-2 flex justify-center w-full">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">A repor...</span>
                    <span v-else>Repor password</span>
                </PrimaryButton>
            </div>

            <div class="pt-2 text-center text-xs text-neutral-400 sm:text-sm">
                <Link
                    :href="route('login')"
                    class="text-neutral-300 transition-colors hover:text-white"
                >
                    Voltar ao início de sessão
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
