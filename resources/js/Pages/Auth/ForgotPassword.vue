<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import route from "ziggy-js";

interface Props {
    status: string;
}

defineProps<Props>();

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <Head title="Forgot Password" />

    <CardLayout title="Repor password" heading="Esqueceste-te da tua password?">
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

            <PrimaryButton disabled="form.processing">
                Enviar email
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
