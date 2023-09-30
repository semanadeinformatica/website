<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Edition from "@/Types/Edition";
import type EventDay from "@/Types/EventDay";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import TextInput from "@/Components/TextInput.vue";

interface Props {
    item: EventDay;
    with: {
        editions: Edition[];
    };
}

const { item: eventDay } = defineProps<Props>();

const form = useForm({
    edition_id: eventDay.edition_id.toString(),
    theme: eventDay.theme,
    date: eventDay.date + "",
});

const submit = () => {
    form.put(route("admin.eventDays.store", eventDay));
};
</script>

<template>
    <CardLayout title="Novo Dia">
        <form class="contents" @submit.prevent="submit">
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
                id="theme"
                v-model="form.theme"
                label="Tema"
                type="text"
                required
                autofocus
                :error-message="form.errors.theme"
            />

            <TextInput
                id="date"
                v-model="form.date"
                label="Data"
                type="date"
                required
                :error-message="form.errors.date"
            />

            <PrimaryButton type="submit">Editar </PrimaryButton>
        </form>
    </CardLayout>
</template>
