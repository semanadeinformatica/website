<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { Lock } from "@lucide/vue";
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
    <AuthLayout
        title="Confirmar password"
        heading="Área protegida"
        subtitle="Esta é uma área segura da aplicação. Por favor, confirma a tua password antes de continuar."
        :icon="Lock"
    >
        <form method="POST" class="flex flex-col gap-5" @submit.prevent="submit">
            <!-- Hidden username field for password manager credential pairing -->
            <input
                type="text"
                name="username"
                :value="$page.props.auth?.user?.email ?? ''"
                autocomplete="username"
                class="sr-only"
                tabindex="-1"
                aria-hidden="true"
            />

            <TextInput
                id="password"
                ref="passwordInput"
                v-model="form.password"
                name="password"
                label="Password"
                type="password"
                required
                autocomplete="current-password"
                autofocus
                :error-message="form.errors.password"
            />

            <div class="mt-2 flex justify-center w-full">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">A confirmar...</span>
                    <span v-else>Confirmar</span>
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>
