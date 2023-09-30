<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type EventDay from "@/Types/EventDay";
import type EventType from "@/Types/EventType";
import type { User } from "@/Types/User";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    with: {
        event_days: EventDay[];
        event_types: EventType[];
        users: User[];
    };
}

defineProps<Props>();

const form = useForm({
    name: "",
    time_start: "",
    time_end: "",
    description: "",
    topic: "",
    capacity: "",
    event_day_id: "",
    event_type_id: "",
    room: "",
    users: [] as string[],
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
                id="time_start"
                v-model="form.time_start"
                label="Hora de início"
                type="time"
                step="60"
                required
                :error-message="form.errors.time_start"
            />

            <TextInput
                id="time_end"
                v-model="form.time_end"
                label="Hora de fim"
                type="time"
                step="60"
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
                id="room"
                v-model="form.room"
                label="Sala"
                type="text"
                required
                :error-message="form.errors.room"
            />

            <TextInput
                id="description"
                v-model="form.description"
                label="Descrição"
                type="textarea"
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
                    {{ $d(day.date, "short") }}
                </option>
            </TextInput>

            <TextInput
                v-model="form.event_type_id"
                type="select"
                required
                label="Tipo do evento"
                :error-message="form.errors.event_type_id"
            >
                <option
                    v-for="_type in $props.with.event_types"
                    :key="_type.id"
                    :value="_type.id"
                >
                    {{ _type.name }}
                </option>
            </TextInput>

            <TextInput
                id="users[]"
                v-model="form.users"
                type="select"
                label="Utilizadores"
                multiple
                :error-message="form.errors.users"
            >
                <option
                    v-for="user in $props.with.users"
                    :key="user.id"
                    :value="user.id"
                >
                    {{ user.name }}
                </option>
            </TextInput>

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
