<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "../../Layouts/CardLayout.vue";
import route from "ziggy-js";

interface Props {
    canResetPassword: boolean;
    status: string;
}

defineProps<Props>();

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <CardLayout title="Iniciar sessão" heading="Inicia sessão!">
        <div v-if="status">
            {{ status }}
        </div>

        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="email"
                v-model="form.email"
                label="Email"
                type="email"
                required
                autofocus
                autocomplete="email"
                :error-message="form.errors.email"
            />

            <TextInput
                id="password"
                v-model="form.password"
                label="Password"
                type="password"
                required
                autocomplete="current-password"
                :error-message="form.errors.password"
            />

            <label class="flex items-center gap-2 self-stretch">
                <Checkbox v-model:checked="form.remember" name="remember" />
                <span class="text-2023-teal">Lembrar-me</span>
            </label>

            <div
                class="flex flex-col items-center gap-2 font-semibold text-2023-teal underline"
            >
                <Link :href="route('register')">Ainda não tens conta?</Link>

                <Link v-if="canResetPassword" :href="route('password.request')">
                    Esqueceste-te da password?
                </Link>
            </div>

            <PrimaryButton :disabled="form.processing"> Entrar </PrimaryButton>
        </form>
    </CardLayout>
</template>
