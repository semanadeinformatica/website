<script setup lang="ts">
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import TextInput from "@/Components/Form/TextInput.vue";

const form = useForm({
    name: "",
});

const submit = () => {
    form.post(route("admin.eventTypes.store"));
};
</script>

<template>
    <CRUDModal
        title="Criar Tipo de Evento"
        name="eventTypes"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
        <TextInput
            id="name"
            v-model="form.name"
            label="Nome do Tipo de Evento"
            type="text"
            required
            autofocus
            :error-message="form.errors.name"
        />
    </CRUDModal>
</template>
