<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import type Competition from "@/Types/Competition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import ImageInput from "@/Components/ImageInput.vue";
import type Participant from "@/Types/Participant";

interface Props {
    with: {
        competitions: Competition[];
        participants: Participant[];
    };
}

defineProps<Props>();

const form = useForm({
    competition_id: "",
    name: "",
    points: "0",
    members: [] as string[],
    image: null as File | null,
});

const submit = () => {
    form.post(route("admin.competitionTeams.store"));
};
</script>

<template>
    <CardLayout title="Associar membro a equipa">
        <form class="contents" @submit.prevent="submit">
            <ImageInput
                id="image"
                v-model="form.image"
                label="Imagem da equipa"
                class="self-stretch"
                :error-message="form.errors.image"
            />

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
                id="points"
                v-model="form.points"
                label="Pontos"
                type="number"
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

            <TextInput
                id="members[]"
                v-model="form.members"
                type="select"
                label="Membros"
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

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
