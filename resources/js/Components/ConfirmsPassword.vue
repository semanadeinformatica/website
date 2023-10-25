<script setup lang="ts">
import { ref, reactive, nextTick } from "vue";
import DialogModal from "./DialogModal.vue";
import PrimaryButton from "./PrimaryButton.vue";
import TextInput from "./TextInput.vue";
import axios from "axios";
import route from "ziggy-js";

interface Props {
    title: string;
    content: string;
    button: string;
}

interface Emits {
    (event: "confirmed"): void;
}

const emit = defineEmits<Emits>();

withDefaults(defineProps<Props>(), {
    title: "Confirmar Password",
    content: "Para a tua segurança, confirma a tua senha para continuar.",
    button: "Confirm",
});

const confirmingPassword = ref(false);

const form = reactive({
    password: "",
    error: "",
    processing: false,
});

const passwordInput = ref<HTMLInputElement | null>(null);

const startConfirmingPassword = () => {
    axios.get(route("password.confirmation")).then((response) => {
        if (response.data.confirmed) {
            emit("confirmed");
        } else {
            confirmingPassword.value = true;

            setTimeout(() => passwordInput.value?.focus(), 250);
        }
    });
};

const confirmPassword = () => {
    form.processing = true;

    axios
        .post(route("password.confirm"), {
            password: form.password,
        })
        .then(() => {
            form.processing = false;

            closeModal();
            nextTick().then(() => emit("confirmed"));
        })
        .catch((error) => {
            form.processing = false;
            form.error = error.response.data.errors.password[0];
            passwordInput.value?.focus();
        });
};

const closeModal = () => {
    confirmingPassword.value = false;
    form.password = "";
    form.error = "";
};
</script>

<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <DialogModal :show="confirmingPassword" @close="closeModal">
            <template #title>
                {{ title }}
            </template>

            <template #content>
                {{ content }}

                <TextInput
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    label="Password"
                    autocomplete="current-password"
                    :error-message="form.error"
                    @keyup.enter="confirmPassword"
                />
            </template>

            <template #footer>
                <PrimaryButton @click="closeModal"> Cancel </PrimaryButton>

                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="confirmPassword"
                >
                    {{ button }}
                </PrimaryButton>
            </template>
        </DialogModal>
    </span>
</template>
