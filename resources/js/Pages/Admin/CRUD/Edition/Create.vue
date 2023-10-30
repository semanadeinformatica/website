<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

const form = useForm({
    name: "",
    year: "",
});

const submit = () => {
    form.post(route("admin.editions.store"));
};
</script>

<template>
    <CardLayout title="Criar edição">
        <form class="contents" @submit.prevent="submit">
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

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
