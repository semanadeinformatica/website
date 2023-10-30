<script setup lang="ts">
import type Quest from "@/Types/Quest";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import CardLayout from "@/Layouts/CardLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ImageInput from "@/Components/ImageInput.vue";

interface Props {
    with: {
        quests: Quest[];
    };
}

defineProps<Props>();

const form = useForm({
    total_quests: "0",
    points: "0",
    name: "",
    quests: [] as string[],
    image: null as File | null,
});

const submit = () => {
    form.post(route("admin.slots.store"));
};
</script>

<template>
    <CardLayout title="Slots">
        <form class="contents" @submit.prevent="submit">
            <ImageInput
                id="image"
                v-model="form.image"
                label="Imagem do slot"
                class="self-stretch"
                :error-message="form.errors.image"
            />

            <TextInput
                id="total_quests"
                v-model="form.total_quests"
                label="Numero mínimo de tarefas"
                type="number"
                :error-message="form.errors.total_quests"
            />

            <TextInput
                id="points"
                v-model="form.points"
                label="Pontos"
                type="number"
                :error-message="form.errors.points"
            />

            <TextInput
                id="name"
                v-model="form.name"
                label="Nome"
                type="text"
                :error-message="form.errors.name"
            />

            <TextInput
                id="quests[]"
                v-model="form.quests"
                type="select"
                label="Tarefas"
                multiple
                :error-message="form.errors.quests"
            >
                <option
                    v-for="quest in $props.with.quests"
                    :key="quest.id"
                    :value="quest.id"
                >
                    {{ quest.name }}
                </option>
            </TextInput>

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
