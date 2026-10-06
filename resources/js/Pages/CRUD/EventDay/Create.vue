<script setup lang="ts">
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";
import TextInput from "@/Components/Form/TextInput.vue";

interface Props {
    with: {
        editions: Edition[];
    };
}

const props = defineProps<Props>();

const currentEdition = computed(() => {
    const possibleEditions = props.with.editions.filter(
        (edition) => edition.year === new Date().getFullYear(),
    );

    return possibleEditions.length === 1 ? possibleEditions[0] : null;
});

const form = useForm({
    edition_id: currentEdition.value?.id.toString() ?? "",
    theme: "",
    date: "",
});

const submit = () => {
    form.post(route("admin.eventDays.store"));
};
</script>

<template>
    <CRUDModal
        title="Criar Dia de Evento"
        name="eventDays"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
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
    </CRUDModal>
</template>
