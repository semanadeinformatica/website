<script setup lang="ts">
import TextInput from "@/Components/Form/TextInput.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const form = useForm({
    name: "",
    year: "",
});

const submit = () => {
    form.post(route("admin.editions.store"));
};
</script>

<template>
    <CRUDModal
        title="Criar edição"
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
