<script setup lang="ts">
import type Quest from "@/Types/Quest";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import CardLayout from "@/Layouts/CardLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import type Slot from "@/Types/Slot";
import ImageInput from "@/Components/ImageInput.vue";

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
    form.post(route("admin.slots.update", slot));
};
</script>

<template>
    <CardLayout title="Slots">
        <form class="contents" @submit.prevent="submit">
            <ImageInput
                id="image"
                v-model="form.image"
                :initial-preview="item.image_slot_url"
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
