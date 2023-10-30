<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

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
    form.put(route("admin.departments.update", department));
};
</script>

<template>
    <CardLayout title="Editar Departamento">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="name"
                v-model="form.name"
                label="Nome"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.name"
            >
            </TextInput>

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
                class="self-stretch"
                :error-message="form.errors.priority"
            />

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
