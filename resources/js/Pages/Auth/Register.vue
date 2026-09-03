<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
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
    <CardLayout title="Registar">
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <TextInput
                id="name"
                v-model="form.name"
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
                    label="Confirmar password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    :error-message="form.errors.password_confirmation"
                />
            </div>

            <div class="space-y-2.5 pt-1">
                <label
                    v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                    class="group flex cursor-pointer items-start gap-3 rounded-2xl border border-white/6 bg-black/30 p-3 text-xs leading-relaxed text-neutral-300 shadow-(--shadow-pill-inset) backdrop-blur-sm transition-all hover:border-white/15 hover:bg-black/45 sm:text-sm"
                >
                    <Checkbox
                        id="terms"
                        v-model:checked="form.terms"
                        name="terms"
                        required
                        class="mt-0.5 shrink-0"
                    />

                    <span>
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
                <span
                    v-if="form.errors.terms"
                    class="ml-3 block text-xs font-medium text-red-400"
                >
                    {{ form.errors.terms }}
                </span>

                <label
                    class="group flex cursor-pointer items-start gap-3 rounded-2xl border border-white/6 bg-black/30 p-3 text-xs leading-relaxed text-neutral-300 shadow-(--shadow-pill-inset) backdrop-blur-sm transition-all hover:border-white/15 hover:bg-black/45 sm:text-sm"
                >
                    <!-- We only need to have this checkbox marked as required for the purpose of this feature to be met,
                    since it makes it so that every account that exists has agreed to this -->
                    <Checkbox
                        id="data_sharing_agreement"
                        v-model:checked="form.data_sharing_agreement"
                        name="data_sharing_agreement"
                        required
                        class="mt-0.5 shrink-0"
                    />

                    <span>
                        Concordo em ter as minhas informações pessoais
                        partilhadas com as empresas que participam na SINF.
                    </span>
                </label>
                <span
                    v-if="form.errors.data_sharing_agreement"
                    class="ml-3 block text-xs font-medium text-red-400"
                >
                    {{ form.errors.data_sharing_agreement }}
                </span>
            </div>

            <PrimaryButton :disabled="form.processing" class="mt-2 w-full">
                <span v-if="form.processing">A criar conta...</span>
                <span v-else>Criar conta</span>
            </PrimaryButton>
        </form>
    </CardLayout>
</template>
