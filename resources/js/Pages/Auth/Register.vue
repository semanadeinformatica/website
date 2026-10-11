<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import Checkbox from "@/Components/Form/Checkbox.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { AlertCircle, UserPlus } from "@lucide/vue";
import { route } from "ziggy-js";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
    data_sharing_agreement: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <AuthLayout
        title="Registar"
        heading="Cria a tua conta"
        subtitle="Junta-te à Semana de Informática 2026 e aproveita palestras inspiradoras, workshops práticos e oportunidades únicas de networking."
        :icon="UserPlus"
    >
        <form
            method="POST"
            class="flex flex-col gap-4"
            @submit.prevent="submit"
        >
            <TextInput
                id="name"
                v-model="form.name"
                name="name"
                label="Nome completo"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="O teu nome"
                :error-message="form.errors.name"
            />

            <TextInput
                id="email"
                v-model="form.email"
                name="email"
                label="Email"
                type="email"
                required
                autocomplete="email"
                placeholder="nome@exemplo.pt"
                :error-message="form.errors.email"
            />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <TextInput
                    id="password"
                    v-model="form.password"
                    name="password"
                    label="Password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    :error-message="form.errors.password"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    name="password_confirmation"
                    label="Confirmar password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    :error-message="form.errors.password_confirmation"
                />
            </div>

            <div class="flex flex-col gap-3 pt-1">
                <div
                    v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                    class="relative flex flex-col items-stretch self-stretch"
                >
                    <label
                        class="pill-container group flex w-full cursor-pointer items-center gap-3.5 px-4 py-2.5 transition-all duration-200 focus-within:border-white/20 focus-within:bg-black/65 hover:border-white/20"
                        :class="
                            form.errors.terms
                                ? 'border-red-500/50 focus-within:border-red-500/60 focus-within:ring-1 focus-within:ring-red-500/30'
                                : ''
                        "
                    >
                        <Checkbox
                            id="terms"
                            v-model:checked="form.terms"
                            name="terms"
                            required
                            class="shrink-0"
                        />

                        <span
                            class="text-xs leading-relaxed text-neutral-300 transition-colors group-hover:text-white sm:text-sm"
                        >
                            Concordo com os
                            <a
                                target="_blank"
                                :href="route('terms.show')"
                                class="font-medium text-white underline underline-offset-2 transition-colors hover:text-neutral-200"
                                >Termos de Serviço</a
                            >
                            e a
                            <a
                                target="_blank"
                                :href="route('policy.show')"
                                class="font-medium text-white underline underline-offset-2 transition-colors hover:text-neutral-200"
                                >Política de Privacidade</a
                            >.
                        </span>
                    </label>

                    <transition
                        enter-active-class="transition ease-out duration-150"
                        enter-from-class="opacity-0 -translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <div
                            v-if="form.errors.terms"
                            class="mt-1.5 ml-3.5 flex items-center gap-1.5 text-xs font-medium text-red-400"
                        >
                            <AlertCircle :size="14" class="shrink-0" />
                            <span>{{ form.errors.terms }}</span>
                        </div>
                    </transition>
                </div>

                <div class="relative flex flex-col items-stretch self-stretch">
                    <label
                        class="pill-container group flex w-full cursor-pointer items-center gap-3.5 px-4 py-2.5 transition-all duration-200 focus-within:border-white/20 focus-within:bg-black/65 hover:border-white/20"
                        :class="
                            form.errors.data_sharing_agreement
                                ? 'border-red-500/50 focus-within:border-red-500/60 focus-within:ring-1 focus-within:ring-red-500/30'
                                : ''
                        "
                    >
                        <!-- We only need to have this checkbox marked as required for the purpose of this feature to be met,
                        since it makes it so that every account that exists has agreed to this -->
                        <Checkbox
                            id="data_sharing_agreement"
                            v-model:checked="form.data_sharing_agreement"
                            name="data_sharing_agreement"
                            required
                            class="shrink-0"
                        />

                        <span
                            class="text-xs leading-relaxed text-neutral-300 transition-colors group-hover:text-white sm:text-sm"
                        >
                            Concordo em ter as minhas informações pessoais
                            partilhadas com as empresas que participam na SINF.
                        </span>
                    </label>

                    <transition
                        enter-active-class="transition ease-out duration-150"
                        enter-from-class="opacity-0 -translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <div
                            v-if="form.errors.data_sharing_agreement"
                            class="mt-1.5 ml-3.5 flex items-center gap-1.5 text-xs font-medium text-red-400"
                        >
                            <AlertCircle :size="14" class="shrink-0" />
                            <span>{{
                                form.errors.data_sharing_agreement
                            }}</span>
                        </div>
                    </transition>
                </div>
            </div>

            <div class="mt-4 flex w-full justify-center">
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">A criar conta...</span>
                    <span v-else>Criar conta</span>
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>
