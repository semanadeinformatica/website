<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import { route } from "ziggy-js";

interface Props {
    canResetPassword?: boolean;
    status?: string;
}

withDefaults(defineProps<Props>(), {
    canResetPassword: true,
    status: undefined,
});

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
    <CardLayout title="Iniciar sessão">
        <div
            v-if="status"
            class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-center text-xs font-medium text-emerald-400"
        >
            {{ status }}
        </div>

        <form class="flex flex-col gap-4" @submit.prevent="submit">
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

            <TextInput
                id="password"
                v-model="form.password"
                label="Password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                :error-message="form.errors.password"
            />

            <div
                class="flex items-center justify-between gap-2 px-1 text-xs sm:text-sm"
            >
                <label
                    class="group flex cursor-pointer items-center gap-2.5 text-neutral-300 select-none"
                >
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="transition-colors group-hover:text-white"
                        >Lembrar-me</span
                    >
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-xs text-neutral-400 transition-colors hover:text-white sm:text-sm"
                >
                    Esqueceste-te da password?
                </Link>
            </div>

            <PrimaryButton :disabled="form.processing" class="mt-2 w-full">
                <span v-if="form.processing">A entrar...</span>
                <span v-else>Entrar</span>
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
