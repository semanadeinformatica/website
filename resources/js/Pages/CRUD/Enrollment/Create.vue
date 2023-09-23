<script setup lang="ts">
import type Participant from "@/Types/Participant";
import type Edition from "@/Types/Edition";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    with: {
        participants: Participant[];
        editions: Edition[];
    };
}

defineProps<Props>();

const form = useForm({
    participant_id: "",
    edition_id: "",
});

const submit = () => {
    form.post(route("admin.enrollments.store"));
};
</script>

<template>
    <CardLayout title="Criar inscrição">
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

            <PrimaryButton type="submit">Adicionar</PrimaryButton>
        </form>
    </CardLayout>
</template>
