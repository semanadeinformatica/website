<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import CardLayout from "@/Layouts/CardLayout.vue";

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <CardLayout title="Repor password" heading="Esqueceste-te da tua password?">
        <div v-if="status">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="contents">
            <TextInput
                label="Email"
                id="email"
                v-model="form.email"
                type="email"
                required
                autofocus
                autocomplete="email"
                :errorMessage="form.errors.email"
            />

            <PrimaryButton disabled="form.processing">
                Enviar email
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
