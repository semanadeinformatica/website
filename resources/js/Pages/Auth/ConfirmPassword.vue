<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import CardLayout from "../../Layouts/CardLayout.vue";

const form = useForm({
    password: '',
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <CardLayout heading="Confirma a tua password">
        <form @submit.prevent="submit" class="contents">
            <TextInput
                label="Password"
                id="password"
                ref="passwordInput"
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
                autofocus
                :errorMessage="form.errors.password"
            />

            <PrimaryButton :disabled="form.processing">
                Confirmo
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
