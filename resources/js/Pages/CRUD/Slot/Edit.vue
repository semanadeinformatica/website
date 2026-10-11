<script setup lang="ts">
import type Quest from "@/Types/Quest";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import type Slot from "@/Types/Slot";
import ImageInput from "@/Components/Form/ImageInput.vue";

interface Props {
    item: Slot;
    with: {
        quests: Quest[];
    };
}

const { item: slot } = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    total_quests: slot.total_quests.toString(),
    points: slot.points.toString(),
    name: slot.name,
    quests: slot.quests?.map((q) => q.id.toString()) ?? [],
    image: null as File | null,
});

const submit = () => {
    form.post(route("admin.slots.update", { slot: slot.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar Encaixe de Tarefas"
        name="slots"
        :processing="form.processing"
        max-width="lg"
        @submit="submit"
    >
        <ImageInput
            id="image"
            v-model="form.image"
            :initial-preview="slot.image_slot_url"
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
