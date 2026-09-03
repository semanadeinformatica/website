<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import { route } from "ziggy-js";

const form = useForm({
    password: "",
});

const passwordInput = ref<HTMLInputElement | null>(null);

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => {
            form.reset();
            passwordInput.value?.focus();
        },
    });
};
</script>

<template>
    <CardLayout
        title="Confirmar password"
        heading="Confirma a tua password"
        subtitle="Esta é uma área protegida. Por favor, confirma a tua password antes de continuar."
    >
        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <TextInput
                id="password"
                ref="passwordInput"
                v-model="form.password"
                label="Password"
                type="password"
                required
                autocomplete="current-password"
                autofocus
                :error-message="form.errors.password"
            />

            <PrimaryButton :disabled="form.processing" class="mt-2 w-full">
                <span v-if="form.processing">A confirmar...</span>
                <span v-else>Confirmar</span>
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
