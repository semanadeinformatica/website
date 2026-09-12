<script setup lang="ts">
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
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
    <CardLayout title="Criar Novo Tipo de Evento">
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

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
