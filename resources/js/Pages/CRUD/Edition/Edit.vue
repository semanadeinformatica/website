<script setup lang="ts">
import TextInput from "@/Components/Form/TextInput.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    item: Edition;
}

const { item: edition } = defineProps<Props>();

const form = useForm({
    name: edition.name,
    year: edition.year.toString(),
});

const submit = () => {
    form.put(route("admin.editions.update", { edition: edition.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar edição"
        name="editions"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
        <TextInput
            id="name"
            v-model="form.name"
            label="Nome"
            type="text"
            required
            autofocus
            autocomplete="name"
            :error-message="form.errors.name"
        />

        <TextInput
            id="year"
            v-model="form.year"
            label="Ano"
            type="number"
            required
            :error-message="form.errors.year"
        />
    </CRUDModal>
</template>
