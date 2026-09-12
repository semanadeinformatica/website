<script setup lang="ts">
import TextInput from "@/Components/Form/TextInput.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import type EventDay from "@/Types/EventDay";
import type EventType from "@/Types/EventType";
import type { User } from "@/Types/User";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

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
    location: "",
    external_url: "",
    enroll_in_site: true,
    users: [] as string[],
});

const submit = () => {
    form.post(route("admin.events.store"));
};
</script>

<template>
    <CRUDModal
        title="Criar evento"
        name="events"
        :processing="form.processing"
        max-width="2xl"
        @submit="submit"
    >
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                id="name"
                v-model="form.name"
                label="Nome do Evento"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.name"
                class="sm:col-span-2"
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
                id="location"
                v-model="form.location"
                label="Local"
                type="text"
                :error-message="form.errors.location"
            />

            <TextInput
                id="time_start"
                v-model="form.time_start"
                label="Hora de início"
                type="time"
                step="1"
                required
                :error-message="form.errors.time_start"
            />

            <TextInput
                id="time_end"
                v-model="form.time_end"
                label="Hora de fim"
                type="time"
                step="1"
                required
                :error-message="form.errors.time_end"
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
                id="capacity"
                v-model="form.capacity"
                label="Capacidade"
                type="number"
                :error-message="form.errors.capacity"
            />

            <TextInput
                id="external_url"
                v-model="form.external_url"
                label="URL Externo"
                type="url"
                :error-message="form.errors.external_url"
            />

            <TextInput
                id="description"
                v-model="form.description"
                label="Descrição"
                type="textarea"
                :error-message="form.errors.description"
                class="sm:col-span-2"
            />

            <div class="sm:col-span-2">
                <TextInput
                    id="users[]"
                    v-model="form.users"
                    type="select"
                    label="Utilizadores / Oradores"
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
            </div>

            <div
                class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3 sm:col-span-2"
            >
                <span class="text-xs font-medium text-neutral-300">
                    Permitir inscrição no site?
                </span>
                <Checkbox
                    id="enroll_in_site"
                    v-model:checked="form.enroll_in_site"
                />
            </div>
        </div>
    </CRUDModal>
</template>
