<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Edition;
}

const { item: edition } = defineProps<Props>();

const form = useForm({
    name: edition.name,
    year: edition.year.toString(),
});

const submit = () => {
    form.put(route("admin.editions.update", edition));
};
</script>

<template>
    <CardLayout title="Editar edição">
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
                id="year"
                v-model="form.year"
                label="Ano"
                type="number"
                required
                :error-message="form.errors.year"
            />

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
