<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "../../Layouts/CardLayout.vue";
import route from "ziggy-js";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <CardLayout title="Registar" heading="Regista-te!">
        <form @submit.prevent="submit" class="contents">
            <TextInput
                label="Name"
                id="name"
                v-model="form.name"
                type="text"
                required
                autofocus
                autocomplete="name"
                :errorMessage="form.errors.name"
            />

            <TextInput
                label="Email"
                id="email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
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

            <label
                v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                class="flex items-center gap-2 self-stretch"
            >
                <Checkbox
                    id="terms"
                    v-model:checked="form.terms"
                    name="terms"
                    required
                />

                <span class="text-2023-teal"
                    >I agree to the
                    <a
                        target="_blank"
                        :href="route('terms.show')"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >Terms of Service</a
                    >
                    and
                    <a
                        target="_blank"
                        :href="route('policy.show')"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >Privacy Policy</a
                    ></span
                >
            </label>

            <Link
                :href="route('login')"
                class="font-semibold text-2023-teal underline"
            >
                Já tens conta?
            </Link>

            <PrimaryButton :disabled="form.processing">
                Criar conta
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
