<script setup lang="ts">
import type Participant from "@/Types/Participant";
import type Edition from "@/Types/Edition";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import type Enrollment from "@/Types/Enrollment";

interface Props {
    item: Enrollment;
    with: {
        participants: Participant[];
        editions: Edition[];
    };
}

const { item: enrollment } = defineProps<Props>();

const form = useForm({
    participant_id: enrollment.participant_id,
    edition_id: enrollment.edition_id,
    points: enrollment.points.toString(),
});

const submit = () => {
    form.put(route("admin.enrollments.update", enrollment));
};
</script>

<template>
    <CardLayout title="Editar inscrição">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="participant_id"
                v-model="form.participant_id"
                label="Participante"
                type="select"
                required
                :error-message="form.errors.participant_id"
            >
                <option
                    v-for="participant in $props.with.participants"
                    :key="participant.id"
                    :value="participant.id"
                >
                    {{ participant.user?.name }} - {{ participant.user?.id }}
                </option>
            </TextInput>

            <TextInput
                id="edition_id"
                v-model="form.edition_id"
                label="Edição"
                type="select"
                required
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
                id="points"
                v-model="form.points"
                label="Pontos"
                type="number"
                :error-message="form.errors.points"
                disabled
            />

            <PrimaryButton type="submit">Adicionar</PrimaryButton>
        </form>
    </CardLayout>
</template>
