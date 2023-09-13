<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Edition from "@/Types/Edition";
import type Event from "@/Types/Event";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Event;
    with: {
        editions: Edition[];
    };
}

const { item: event } = defineProps<Props>();

const form = useForm({
    name: event.name,
    date_start: event.date_start.substring(0, 16),
    date_end: event.date_end.substring(0, 16),
    topic: event.topic,
    capacity: event.capacity?.toString() ?? "",
    edition_id: event.edition_id,
});

const submit = () => {
    form.put(route("admin.events.update", event));
};
</script>

<template>
    <CardLayout title="Editar evento">
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
                required
                :error-message="form.errors.date_start"
            />

            <TextInput
                id="date_end"
                v-model="form.date_end"
                label="Data de fim"
                type="datetime-local"
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

            <select
                v-model="form.edition_id"
                required
                class="self-stretch"
            >
                <option value="" disabled selected hidden>
                    Edição
                </option>
                <option
                    v-for="edition in $props.with.editions"
                    :key="edition.id"
                    :value="edition.id"
                >
                    {{ edition.name }}
                </option>
            </select>

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
