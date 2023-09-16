<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Event from "@/Types/Event";
import type EventDay from "@/Types/EventDay";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Event;
    with: {
        event_days: EventDay[];
    };
}

const { item: event } = defineProps<Props>();

const form = useForm({
    name: event.name,
    time_start: event.time_start.substring(0, 16),
    time_end: event.time_end.substring(0, 16),
    topic: event.topic,
    description: event.description,
    capacity: event.capacity?.toString() ?? "",
    event_day_id: event.event_day_id.toString(),
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
                id="time_start"
                v-model="form.time_start"
                label="Hora de início"
                type="time"
                required
                :error-message="form.errors.time_start"
            />

            <TextInput
                id="time_end"
                v-model="form.time_end"
                label="Hora de fim"
                type="time"
                required
                :error-message="form.errors.time_end"
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
                id="description"
                v-model="form.description"
                label="Descrição"
                type="text"
                step="60"
                required
                :error-message="form.errors.description"
            />

            <TextInput
                id="capacity"
                v-model="form.capacity"
                label="Capacidade"
                type="number"
                :error-message="form.errors.capacity"
            />

            <TextInput
                v-model="form.event_day_id"
                type="select"
                required
                label="Dia do evento"
                :error-message="form.errors.event_day_id"
            >
                <option
                    v-for="day in $props.with.event_days"
                    :key="day.id"
                    :value="day.id"
                >
                    {{ day.date }}
                </option>
            </TextInput>

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
