<script setup lang="ts">
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import type Competition from "@/Types/Competition";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import ImageInput from "@/Components/Form/ImageInput.vue";

interface Props {
    with: {
        competitions: Competition[];
    };
}

defineProps<Props>();

const form = useForm({
    competition_id: "",
    place: 0,
    prize_picture: null as File | null,
});

const submit = () => {
    form.post(route("admin.competitionPrizes.store"));
};
</script>

<template>
    <CRUDModal
        title="Associar Prémio a Competição"
        name="competitionPrizes"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
        <ImageInput
            id="image"
            v-model="form.prize_picture"
            label="Imagem do prémio"
            class="self-stretch"
            :error-message="form.errors.prize_picture"
        />

        <TextInput
            id="place"
            v-model="form.place"
            label="Lugar (Posição)"
            type="number"
            required
            autofocus
            autocomplete="place"
            :error-message="form.errors.place"
        />

        <TextInput
            v-model="form.competition_id"
            type="select"
            required
            label="Competição"
            :error-message="form.errors.competition_id"
        >
            <option
                v-for="competition in $props.with.competitions"
                :key="competition.id"
                :value="competition.id"
            >
                {{ competition.name }}
            </option>
        </TextInput>
    </CRUDModal>
</template>
