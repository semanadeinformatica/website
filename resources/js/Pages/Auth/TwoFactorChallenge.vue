<script setup lang="ts">
import { nextTick, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import route from "ziggy-js";
import CardLayout from "@/Layouts/CardLayout.vue";

const recovery = ref(false);

const form = useForm({
    code: "",
    recovery_code: "",
});

const recoveryCodeInput = ref<HTMLInputElement | null>(null);
const codeInput = ref<HTMLInputElement | null>(null);

const toggleRecovery = async () => {
    recovery.value = !recovery.value;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value?.focus();
        form.code = "";
    } else {
        codeInput.value?.focus();
        form.recovery_code = "";
    }
};

const submit = () => {
    form.post(route("two-factor.login"));
};
</script>

<template>
    <CardLayout title="Confirmar Two-factor">
        <div class="mb-4 text-sm text-gray-600">
            <template v-if="!recovery">
                Please confirm access to your account by entering the
                authentication code provided by your authenticator application.
            </template>

            <template v-else>
                Please confirm access to your account by entering one of your
                emergency recovery codes.
            </template>
        </div>

        <form @submit.prevent="submit">
            <TextInput
                v-if="!recovery"
                id="code"
                ref="codeInput"
                v-model="form.code"
                label="Code"
                type="text"
                inputmode="numeric"
                autofocus
                autocomplete="one-time-code"
                :error-message="form.errors.code"
            />

            <TextInput
                v-else
                id="recovery_code"
                ref="recoveryCodeInput"
                v-model="form.recovery_code"
                label="Recovery Code"
                type="text"
                class="mt-1 block w-full"
                autocomplete="one-time-code"
                :error-message="form.errors.recovery_code"
            />

            <div class="mt-4 flex items-center justify-end">
                <button
                    type="button"
                    class="cursor-pointer text-sm text-gray-600 underline hover:text-gray-900"
                    @click.prevent="toggleRecovery"
                >
                    <template v-if="!recovery"> Use a recovery code </template>

                    <template v-else> Use an authentication code </template>
                </button>

                <PrimaryButton
                    class="ml-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </CardLayout>
</template>
