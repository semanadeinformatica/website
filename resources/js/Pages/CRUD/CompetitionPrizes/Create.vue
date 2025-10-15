<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import type Competition from "@/Types/Competition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import ImageInput from "@/Components/ImageInput.vue";

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
    <CardLayout title="Associar prémio a competição">
        <form class="contents" @submit.prevent="submit">
            <ImageInput
                id="image"
                v-model="form.prize_picture"
                label="Imagem da equipa"
                class="self-stretch"
                :error-message="form.errors.prize_picture"
            />

            <TextInput
                id="place"
                v-model="form.place"
                label="Lugar"
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


            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>

