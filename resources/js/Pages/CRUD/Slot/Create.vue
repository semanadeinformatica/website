<script setup lang="ts">
import type Quest from "@/Types/Quest";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import ImageInput from "@/Components/Form/ImageInput.vue";

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
    <CRUDModal
        title="Criar Encaixe de Tarefas"
        name="slots"
        :processing="form.processing"
        max-width="lg"
        @submit="submit"
    >
        <ImageInput
            id="image"
            v-model="form.image"
            label="Imagem do slot"
            class="self-stretch"
            :error-message="form.errors.image"
        />

        <TextInput
            id="name"
            v-model="form.name"
            label="Nome do Encaixe"
            type="text"
            required
            :error-message="form.errors.name"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                id="total_quests"
                v-model="form.total_quests"
                label="Número mínimo de tarefas"
                type="number"
                :error-message="form.errors.total_quests"
            />

            <TextInput
                id="points"
                v-model="form.points"
                label="Pontos atribuídos"
                type="number"
                :error-message="form.errors.points"
            />
        </div>

        <TextInput
            id="quests[]"
            v-model="form.quests"
            type="select"
            label="Tarefas Associadas"
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
    </CRUDModal>
</template>
