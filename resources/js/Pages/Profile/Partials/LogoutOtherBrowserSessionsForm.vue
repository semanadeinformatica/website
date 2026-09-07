<script setup lang="ts">
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Card from "@/Components/UI/Card.vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Laptop, Smartphone } from "@lucide/vue";
import { route } from "ziggy-js";
import type Session from "@/Types/Session";

interface Props {
    sessions: Session[];
}

defineProps<Props>();

const confirmingLogout = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: "",
});

const confirmLogout = () => {
    confirmingLogout.value = true;
    setTimeout(() => passwordInput.value?.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
    form.delete(route("other-browser-sessions.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;
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
                    Sessões Ativas
                </h2>
                <p class="text-xs text-neutral-400 sm:text-sm">
                    Gere os dispositivos e navegadores com sessão iniciada na
                    tua conta.
                </p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="text-sm text-neutral-300">
                <p>
                    Se suspeitares de atividade não autorizada ou se deixaste a
                    sessão aberta num computador partilhado, podes encerrar
                    todas as outras sessões ativas de imediato.
                </p>
            </div>

            <div v-if="sessions.length > 0" class="space-y-3 pt-1">
                <div
                    v-for="(session, i) in sessions"
                    :key="i"
                    class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/3 p-3.5 sm:p-4"
                >
                    <div class="flex items-center gap-3.5">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 text-neutral-300 ring-1 ring-white/10"
                        >
                            <Laptop
                                v-if="session.agent.is_desktop"
                                :size="20"
                            />
                            <Smartphone v-else :size="20" />
                        </div>

                        <div class="space-y-0.5">
                            <div class="text-sm font-semibold text-white">
                                {{
                                    session.agent.platform ||
                                    "Dispositivo desconhecido"
                                }}
                                <span class="font-normal text-neutral-500"
                                    >•</span
                                >
                                {{
                                    session.agent.browser ||
                                    "Navegador desconhecido"
                                }}
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-2 text-xs text-neutral-400"
                            >
                                <span>{{ session.ip_address }}</span>
                                <span class="text-neutral-600">•</span>
                                <span
                                    v-if="session.is_current_device"
                                    class="font-semibold text-emerald-400"
                                >
                                    Este dispositivo
                                </span>
                                <span v-else>
                                    Última atividade: {{ session.last_active }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex w-full items-center justify-end gap-3">
                <span
                    v-show="form.recentlySuccessful"
                    class="text-xs font-medium text-emerald-400"
                >
                    Sessões terminadas com sucesso.
                </span>

                <PrimaryButton type="button" @click="confirmLogout">
                    Terminar outras sessões
                </PrimaryButton>
            </div>
        </template>
    </Card>

    <!-- Modal for Confirming Logout -->
    <Modal
        v-model="confirmingLogout"
        max-width="md"
        title="Terminar Outras Sessões"
        description="Por favor, confirma a tua palavra-passe para encerrar a sessão em todos os outros dispositivos."
        @close="closeModal"
    >
        <div class="pt-2">
            <TextInput
                ref="passwordInput"
                v-model="form.password"
                type="password"
                label="Palavra-passe"
                autocomplete="current-password"
                :error-message="form.errors.password"
                @keyup.enter="logoutOtherBrowserSessions"
            />
        </div>

        <template #footer>
            <PrimaryButton color="pill" @click="closeModal">
                Cancelar
            </PrimaryButton>

            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="logoutOtherBrowserSessions"
            >
                Terminar sessões
            </PrimaryButton>
        </template>
    </Modal>
</template>
