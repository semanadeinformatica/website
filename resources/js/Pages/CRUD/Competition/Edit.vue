<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import type Competition from "@/Types/Competition";

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
    date_start: new Date(competition.date_start).toDateString(),
    date_end: new Date(competition.date_end).toDateString(),
    name: competition.name,
});

const submit = () => {
    form.put(route("admin.competitions.update", competition));
};
</script>

<template>
    <CardLayout title="Editar Competição">
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
                id="theme"
                v-model="form.theme"
                label="Tema"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.theme"
            />

            <TextInput
                id="date_start"
                v-model="form.date_start"
                label="Data de início"
                type="date"
                required
                autofocus
                :error-message="form.errors.date_start"
            />

            <TextInput
                id="date_end"
                v-model="form.date_end"
                label="Data de fim"
                type="date"
                required
                autofocus
                :error-message="form.errors.date_end"
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

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
