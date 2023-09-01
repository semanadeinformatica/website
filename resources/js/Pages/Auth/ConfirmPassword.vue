<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "../../Layouts/CardLayout.vue";
import route from "ziggy-js";

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
    <CardLayout title="Confirmar password" heading="Confirma a tua password">
        <form class="contents" @submit.prevent="submit">
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

            <PrimaryButton :disabled="form.processing">
                Confirmo
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
