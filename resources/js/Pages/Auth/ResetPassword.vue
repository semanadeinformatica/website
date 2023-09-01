<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "../../Layouts/CardLayout.vue";
import route from "ziggy-js";

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
    <CardLayout title="Repor password" heading="Repõe a tua password">
        <form class="contents" @submit.prevent="submit">
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

            <PrimaryButton :disabled="form.processing">
                Repor password
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
