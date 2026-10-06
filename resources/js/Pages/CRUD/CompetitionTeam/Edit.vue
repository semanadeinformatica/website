<script setup lang="ts">
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import type Competition from "@/Types/Competition";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type CompetitionTeam from "@/Types/CompetitionTeam";
import ImageInput from "@/Components/Form/ImageInput.vue";
import type Participant from "@/Types/Participant";

interface Props {
    item: CompetitionTeam;
    with: {
        competitions: Competition[];
        participants: Participant[];
    };
}

const { item: competitionTeam } = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    competition_id: competitionTeam.competition_id + "",
    name: competitionTeam.name,
    points: competitionTeam.points + "",
    members: competitionTeam.members?.map((p) => p.id.toString()) ?? [],
    image: null as File | null,
});

const submit = () => {
    form.post(
        route("admin.competitionTeams.update", {
            competitionTeam: competitionTeam.id,
        }),
    );
};
</script>

<template>
    <CRUDModal
        title="Editar Equipa"
        name="competitionTeams"
        :processing="form.processing"
        max-width="lg"
        @submit="submit"
    >
        <ImageInput
            id="image"
            v-model="form.image"
            :initial-preview="competitionTeam.image_competition_team_url"
            label="Imagem da equipa"
            class="self-stretch"
            :error-message="form.errors.image"
        />

        <TextInput
            id="name"
            v-model="form.name"
            label="Nome da equipa"
            type="text"
            required
            autofocus
            autocomplete="name"
            :error-message="form.errors.name"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                id="points"
                v-model="form.points"
                label="Pontos"
                type="number"
                required
                autocomplete="points"
                :error-message="form.errors.points"
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
        </div>

        <TextInput
            id="members[]"
            v-model="form.members"
            type="select"
            label="Membros da Equipa"
            multiple
            :error-message="form.errors.members"
        >
            <option
                v-for="participant in $props.with.participants"
                :key="participant.id"
                :value="participant.id"
            >
                {{ participant.user?.name ?? participant.id }}
            </option>
        </TextInput>
    </CRUDModal>
</template>
