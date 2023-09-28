<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import TextInput from "@/Components/TextInput.vue";
import type EventType from "@/Types/EventType";

interface Props {
    item: EventType;
}

const {item: eventType} = defineProps<Props>();

const form = useForm({
    name: eventType.name,
});

const submit = () => {
    form.put(route("admin.eventTypes.update", eventType));
};
</script>

<template>
    <CardLayout title="Editar Tipo de Evento">
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

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
