<script setup lang="ts">
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import type Competition from "@/Types/Competition";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import ImageInput from "@/Components/Form/ImageInput.vue";
import type Participant from "@/Types/Participant";
import type { CompetitionPrizes } from "@/Types/Competition";

interface Props {
    item: CompetitionPrizes;
    with: {
        competitions: Competition[];
        participants: Participant[];
    };
}

const { item: competitionPrize } = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    competition_id: competitionPrize.competition_id + "",
    place: competitionPrize.place + "",
    prize_picture: null as File | null,
});

const submit = () => {
    form.post(
        route("admin.competitionPrizes.update", {
            competitionPrize: competitionPrize.id,
        }),
    );
};
</script>

<template>
    <CRUDModal
        title="Editar Prémio de Competição"
        name="competitionPrizes"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
        <ImageInput
            id="image"
            v-model="form.prize_picture"
            :initial-preview="competitionPrize.prize_picture"
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
