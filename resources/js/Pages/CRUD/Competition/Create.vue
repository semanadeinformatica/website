<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import slugify from "slugify";
import { watchEffect } from "vue";

interface Props {
    with: {
        editions: Edition[];
    };
}

defineProps<Props>();

const form = useForm({
    edition_id: "",
    date_start: "",
    date_end: "",
    theme: "",
    name: "",
    slug: "",
    registration_link: "",
    regulation: "",
});

watchEffect(() => {
    form.slug = slugify(form.name, { lower: true });
});

const submit = () => {
    form.post(route("admin.competitions.store"));
};
</script>

<template>
    <CardLayout title="Criar Competição">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="name"
                v-model="form.name"
                label="Nome da competição"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.theme"
            />

            <TextInput
                id="slug"
                v-model="form.slug"
                label="Nome que aparece no URL"
                type="text"
                required
                :error-message="form.errors.slug"
            />

            <TextInput
                id="theme"
                v-model="form.theme"
                label="Tema"
                type="text"
                required
                autocomplete="theme"
                :error-message="form.errors.theme"
            />

            <TextInput
                id="regulation"
                v-model="form.regulation"
                label="Regulamento"
                type="textarea"
                :error-message="form.errors.regulation"
            />

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

            <TextInput
                id="registration_link"
                v-model="form.registration_link"
                label="Link para registo"
                type="text"
                required
                :error-message="form.errors.registration_link"
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

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
