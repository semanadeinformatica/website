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
    competition_id: competitionTeam.competition_id,
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

            <select v-model="form.competition_id" required class="self-stretch">
                <option value="" disabled selected hidden>Competição</option>
                <option
                    v-for="competition in $props.with.competitions"
                    :key="competition.id"
                    :value="competition.id"
                >
                    {{ competition.id }}
                </option>
            </select>

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
