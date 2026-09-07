<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Card from "@/Components/UI/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { route } from "ziggy-js";

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("user-password.update"), {
        errorBag: "updatePassword",
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                passwordInput.value?.focus();
            }

            if (form.errors.current_password) {
                form.reset("current_password");
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <Card as="section" :interactive="false" padding="p-6 sm:p-8">
        <template #header>
            <div class="space-y-1">
                <h2
                    class="text-lg font-bold tracking-tight text-white sm:text-xl"
                >
                    Palavra-passe
                </h2>
                <p class="text-xs text-neutral-400 sm:text-sm">
                    Garante que a tua conta usa uma palavra-passe forte e segura
                    para proteger os teus dados.
                </p>
            </div>
        </template>

        <form
            id="update-password-form"
            class="flex flex-col gap-4"
            @submit.prevent="updatePassword"
        >
            <TextInput
                id="current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                label="Palavra-passe Atual"
                type="password"
                autocomplete="current-password"
                :error-message="form.errors.current_password"
            />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    label="Nova Palavra-passe"
                    type="password"
                    autocomplete="new-password"
                    :error-message="form.errors.password"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    label="Confirmar Palavra-passe"
                    type="password"
                    autocomplete="new-password"
                    :error-message="form.errors.password_confirmation"
                />
            </div>
        </form>

        <template #footer>
            <div class="flex w-full items-center justify-end gap-3">
                <span
                    v-show="form.recentlySuccessful"
                    class="text-xs font-medium text-emerald-400"
                >
                    Palavra-passe atualizada.
                </span>

                <PrimaryButton
                    type="submit"
                    form="update-password-form"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Atualizar palavra-passe
                </PrimaryButton>
            </div>
        </template>
    </Card>
</template>
