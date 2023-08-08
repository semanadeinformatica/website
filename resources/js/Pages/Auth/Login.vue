<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import CardLayout from "../../Layouts/CardLayout.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <CardLayout title="Log in" heading="Inicia sessão!">
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
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
            />
            <InputError class="mt-2" :message="form.errors.email" />

            <TextInput
                label="Password"
                id="password"
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
            />
            <InputError class="mt-2" :message="form.errors.password" />

            <label class="flex items-center gap-2 self-stretch">
                <Checkbox v-model:checked="form.remember" name="remember" />
                <span class="text-2023-teal">Remember me</span>
            </label>

            <div class="text-2023-teal font-semibold underline flex flex-col gap-2 items-center">
                <Link :href="route('register')">Ainda não tens conta?</Link>
    
                <Link v-if="canResetPassword" :href="route('password.request')">
                    Esqueceste-te da password?
                </Link>
            </div>

            <PrimaryButton :disabled="form.processing">
                Log in
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
