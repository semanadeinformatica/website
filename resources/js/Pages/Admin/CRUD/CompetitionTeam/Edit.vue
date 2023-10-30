<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import type Competition from "@/Types/Competition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import type CompetitionTeam from "@/Types/CompetitionTeam";

interface Props {
    item: CompetitionTeam;
    with: {
        competitions: Competition[];
    };
}

const { item: competitionTeam } = defineProps<Props>();

const form = useForm({
    competition_id: competitionTeam.competition_id + "",
    name: competitionTeam.name,
    points: competitionTeam.points + "",
});

const submit = () => {
    form.put(route("admin.competitionTeams.update", competitionTeam));
};
</script>

<template>
    <CardLayout title="Associar membro a equipa">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="name"
                v-model="form.name"
                label="Tema"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.name"
            />

            <TextInput
                id="points"
                v-model="form.points"
                label="Pontos"
                type="number"
                required
                autofocus
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
                    {{ competition.edition?.name ?? competition.id }}
                </option>
            </TextInput>

            <PrimaryButton type="submit">Associar</PrimaryButton>
        </form>
    </CardLayout>
</template>
