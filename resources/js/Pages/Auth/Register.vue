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
        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="name"
                v-model="form.name"
                label="Nome"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.name"
            />

            <TextInput
                id="email"
                v-model="form.email"
                label="Email"
                type="email"
                required
                autocomplete="email"
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

            <label class="flex items-center gap-2 self-stretch">
                <!-- We only need to have this checkbox marked as required for the purpose of this feature to be met,
                since it makes it so that every account that exists has agreed to this -->
                <Checkbox
                    id="data_sharing_agreement"
                    name="data_sharing_agreement"
                    required
                />

                Concordo em ter as minhas informações pessoais partilhadas com
                as empresas que participam na SINF.
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
