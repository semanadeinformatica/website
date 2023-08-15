<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import CardLayout from "../../Layouts/CardLayout.vue";

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <CardLayout title="Repor password" heading="Repõe a tua password">
        <form @submit.prevent="submit" class="contents">
            <TextInput
                label="Email"
                id="email"
                v-model="form.email"
                type="email"
                required
                autofocus
                autocomplete="username"
                :errorMessage="form.errors.email"
            />

            <TextInput
                label="Password"
                id="password"
                v-model="form.password"
                type="password"
                required
                autocomplete="new-password"
                :errorMessage="form.errors.password"
            />

            <TextInput
                label="Confirmar password"
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                :errorMessage="form.errors.password_confirmation"
            />

            <PrimaryButton :disabled="form.processing">
                Repor password
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
