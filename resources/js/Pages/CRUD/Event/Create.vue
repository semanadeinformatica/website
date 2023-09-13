<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
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
    name: "",
    date_start: "",
    date_end: "",
    topic: "",
    capacity: "",
    edition_id: "",
});

const submit = () => {
    form.post(route("admin.events.store"));
};
</script>

<template>
    <CardLayout title="Criar evento">
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
                id="date_start"
                v-model="form.date_start"
                label="Data de início"
                type="datetime-local"
                step="60"
                required
                :error-message="form.errors.date_start"
            />

            <TextInput
                id="date_end"
                v-model="form.date_end"
                label="Data de fim"
                type="datetime-local"
                step="60"
                required
                :error-message="form.errors.date_end"
            />

            <TextInput
                id="topic"
                v-model="form.topic"
                label="Tópico"
                type="text"
                required
                :error-message="form.errors.topic"
            />

            <TextInput
                id="capacity"
                v-model="form.capacity"
                label="Capacidade"
                type="number"
                :error-message="form.errors.capacity"
            />

            <select v-model="form.edition_id" required class="self-stretch">
                <option value="" disabled selected hidden>Edição</option>
                <option
                    v-for="edition in $props.with.editions"
                    :key="edition.id"
                    :value="edition.id"
                >
                    {{ edition.name }}
                </option>
            </select>

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
