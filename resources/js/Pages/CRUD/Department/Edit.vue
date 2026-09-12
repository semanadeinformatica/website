<script setup lang="ts">
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    item: Department;
    with: {
        editions: Edition[];
    };
}

const { item: department } = defineProps<Props>();

const form = useForm({
    edition_id: department.edition_id.toString(),
    name: department.name,
    priority: department.priority.toString(),
});

const submit = () => {
    form.put(route("admin.departments.update", { department: department.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar Departamento"
        name="departments"
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

        <TextInput
            v-model="form.priority"
            type="number"
            label="Prioridade"
            :error-message="form.errors.priority"
        />
    </CRUDModal>
</template>
