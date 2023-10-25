<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import route from "ziggy-js";

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
    <FormSection @submitted="updatePassword">
        <template #title> Atualizar Password </template>

        <template #description>
            Garante que a tua conta usa uma senha longa e aleatória para se
            manter segura.
        </template>

        <template #form>
            <TextInput
                id="current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                label="Password Atual"
                type="password"
                autocomplete="current-password"
                :error-message="form.errors.current_password"
            />

            <TextInput
                id="password"
                ref="passwordInput"
                v-model="form.password"
                label="Nova Password"
                type="password"
                autocomplete="new-password"
                :error-message="form.errors.password"
            />

            <TextInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                label="Confirmar Password"
                type="password"
                autocomplete="new-password"
                :error-message="form.errors.password_confirmation"
            />
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Guardado.
            </ActionMessage>

            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Guardar
            </PrimaryButton>
        </template>
    </FormSection>
</template>
