<script setup lang="ts">
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import TextInput from "@/Components/Form/TextInput.vue";
import type EventType from "@/Types/EventType";

interface Props {
    item: EventType;
}

const { item: eventType } = defineProps<Props>();

const form = useForm({
    name: eventType.name,
});

const submit = () => {
    form.put(route("admin.eventTypes.update", { eventType: eventType.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar Tipo de Evento"
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
