<script setup lang="ts">
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type Competition from "@/Types/Competition";
import slugify from "slugify";
import { watchEffect } from "vue";

interface Props {
    item: Competition;
    with: {
        editions: Edition[];
    };
}

const { item: competition } = defineProps<Props>();

const form = useForm({
    edition_id: competition.edition_id.toString(),
    theme: competition.theme,
    date_start: competition.date_start.substring(0, 16),
    date_end: competition.date_end.substring(0, 16),
    name: competition.name,
    slug: competition.slug,
    registration_link: competition.registration_link,
    regulation: competition.regulation ?? "",
    description: competition.description ?? "",
});

const submit = () => {
    form.put(
        route("admin.competitions.update", { competition: competition.id }),
    );
};

watchEffect(() => {
    form.slug = slugify(form.name, { lower: true });
});
</script>

<template>
    <CardLayout title="Editar Competição" max-width="max-w-2xl">
        <template #header>
            <div class="space-y-1">
                <h1
                    class="text-xl font-bold tracking-tight text-white sm:text-2xl"
                >
                    Editar Competição
                </h1>
                <p class="text-xs text-neutral-400 sm:text-sm">
                    Atualiza as informações, regulamento e datas da competição.
                </p>
            </div>
        </template>

        <form
            id="competition-edit-form"
            class="flex flex-col gap-4"
            @submit.prevent="submit"
        >
            <TextInput
                id="name"
                v-model="form.name"
                label="Nome da competição"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.name"
            />

            <TextInput
                id="slug"
                v-model="form.slug"
                label="Nome que aparece no URL"
                type="text"
                required
                :error-message="form.errors.slug"
            />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <TextInput
                    id="theme"
                    v-model="form.theme"
                    label="Tema"
                    type="text"
                    required
                    :error-message="form.errors.theme"
                />

                <TextInput
                    v-model="form.edition_id"
                    type="select"
                    required
                    label="Edição"
                    :error-message="form.errors.edition_id"
                >
                    <option
                        v-for="edition in $props.with.editions"
                        :key="edition.id"
                        :value="edition.id"
                    >
                        {{ edition.name }}
                    </option>
                </TextInput>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <TextInput
                    id="date_start"
                    v-model="form.date_start"
                    label="Data de início"
                    type="datetime-local"
                    required
                    :error-message="form.errors.date_start"
                />

                <TextInput
                    id="date_end"
                    v-model="form.date_end"
                    label="Data de fim"
                    type="datetime-local"
                    required
                    :error-message="form.errors.date_end"
                />
            </div>

            <TextInput
                id="registration_link"
                v-model="form.registration_link"
                label="Link para registo"
                type="text"
                required
                :error-message="form.errors.registration_link"
            />

            <TextInput
                id="description"
                v-model="form.description"
                label="Descrição"
                type="textarea"
                :error-message="form.errors.description"
            />

            <TextInput
                id="regulation"
                v-model="form.regulation"
                label="Regulamento"
                type="textarea"
                :error-message="form.errors.regulation"
            />
        </form>

        <template #footer>
            <div class="flex w-full items-center justify-end">
                <PrimaryButton
                    type="submit"
                    form="competition-edit-form"
                    :disabled="form.processing"
                >
                    Guardar alterações
                </PrimaryButton>
            </div>
        </template>
    </CardLayout>
</template>
