<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    with: {
        editions: Edition[];
    };
}

defineProps<Props>();

const form = useForm({
    edition_id: "",
    name: "",
    priority: "",
});

const submit = () => {
    form.post(route("admin.departments.store"));
};
</script>

<template>
    <CardLayout title="Criar Departamento">
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
            />

            <TextInput
                v-model="form.edition_id"
                type="select"
                required
                label="Edição"
                class="self-stretch"
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

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
