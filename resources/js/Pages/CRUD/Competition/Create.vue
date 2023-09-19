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
    date_start: "",
    date_end: "",
    theme: "",
});

const submit = () => {
    form.post(route("admin.competitions.store"));
};
</script>

<template>
    <CardLayout title="Criar Competição">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="theme"
                v-model="form.theme"
                label="Tema"
                type="text"
                required
                autofocus
                autocomplete="theme"
                :error-message="form.errors.theme"
            />

            <TextInput
                id="date_start"
                v-model="form.date_start"
                label="Data de início"
                type="date"
                required
                autofocus
                :error-message="form.errors.date_start"
            />

            <TextInput
                id="date_end"
                v-model="form.date_end"
                label="Data de fim"
                type="date"
                required
                autofocus
                :error-message="form.errors.date_end"
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
