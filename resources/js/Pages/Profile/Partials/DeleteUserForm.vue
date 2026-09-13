<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Card from "@/Components/UI/Card.vue";
import Modal from "@/Components/UI/Modal.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import { AlertTriangle } from "@lucide/vue";
import { route } from "ziggy-js";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";

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
    <Card as="section" :interactive="false" padding="p-6 sm:p-8">
        <template #header>
            <div class="space-y-1">
                <h2
                    class="text-lg font-bold tracking-tight text-white sm:text-xl"
                >
                    Apagar Conta
                </h2>
                <p class="text-xs text-neutral-400 sm:text-sm">
                    Remove permanentemente a tua conta e todos os dados
                    associados.
                </p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="text-sm text-neutral-300">
                <p>
                    Depois de apagada, a tua conta não poderá ser recuperada.
                    Todos os bilhetes adquiridos, pontuações de atividades,
                    submissões e histórico de eventos serão apagados
                    permanentemente.
                </p>
            </div>
        </div>

        <template #footer>
            <div class="flex w-full items-center justify-end">
                <PrimaryButton color="danger" @click="confirmUserDeletion">
                    Apagar conta
                </PrimaryButton>
            </div>
        </template>
    </Card>

    <!-- Modal for Confirming User Deletion -->
    <Modal
        v-model="confirmingUserDeletion"
        max-width="md"
        title="Confirmar Eliminação da Conta"
        description="Esta ação é permanente e não poderá ser desfeita."
        @close="closeModal"
    >
        <div class="space-y-4 pt-1">
            <div
                class="flex items-start gap-3 rounded-2xl border border-red-500/20 bg-red-500/10 p-3.5 text-xs text-red-300"
            >
                <AlertTriangle :size="18" class="shrink-0 text-red-400" />
                <p>
                    Tens a certeza absoluta de que pretendes prosseguir? Por
                    motivos de segurança, introduz a tua palavra-passe para
                    confirmar.
                </p>
            </div>

            <TextInput
                ref="passwordInput"
                v-model="form.password"
                type="password"
                label="Palavra-passe de confirmação"
                autocomplete="current-password"
                :error-message="form.errors.password"
                @keyup.enter="deleteUser"
            />
        </div>

        <template #footer>
            <PrimaryButton color="pill" @click="closeModal">
                Cancelar
            </PrimaryButton>

            <PrimaryButton
                color="danger"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="deleteUser"
            >
                Sim, apagar conta
            </PrimaryButton>
        </template>
    </Modal>
</template>
