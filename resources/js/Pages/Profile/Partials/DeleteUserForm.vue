<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import ActionSection from "@/Components/ActionSection.vue";
import DialogModal from "@/Components/DialogModal.vue";
import TextInput from "@/Components/TextInput.vue";
import route from "ziggy-js";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value?.focus(), 250);
};

const deleteUser = () => {
    form.delete(route("current-user.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title> Delete Account </template>

        <template #description> Permanently delete your account. </template>

        <template #content>
            <div class="max-w-xl text-sm text-2023-red-dark">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </div>

            <div class="mt-5">
                <PrimaryButton color="red" @click="confirmUserDeletion">
                    Delete Account
                </PrimaryButton>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title> Delete Account </template>

                <template #content>
                    Are you sure you want to delete your account? Once your
                    account is deleted, all of its resources and data will be
                    permanently deleted. Please enter your password to confirm
                    you would like to permanently delete your account.

                    <TextInput
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        label="Password"
                        autocomplete="current-password"
                        :error-message="form.errors.password"
                        @keyup.enter="deleteUser"
                    />
                </template>

                <template #footer>
                    <PrimaryButton @click="closeModal"> Cancel </PrimaryButton>

                    <PrimaryButton
                        class="ml-3"
                        color="red"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Delete Account
                    </PrimaryButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
