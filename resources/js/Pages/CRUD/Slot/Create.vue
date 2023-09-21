<script setup lang="ts">
import type Quest from "@/Types/Quest";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import CardLayout from "@/Layouts/CardLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

interface Props {
    with: {
        quests: Quest[];
    };
}

defineProps<Props>();

const form = useForm({
    total_quests: "0",
    points: "0",
    selected_quests: [] as string[],
});

const submit = () => {
    form.post(route("admin.slots.store"));
};
</script>

<template>
    <CardLayout title="Slots">
        <form class="contents" @submit.prevent="submit">
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
                id="selected_quests[]"
                v-model="form.selected_quests"
                type="select"
                label="Tarefas"
                multiple
                :error-message="form.errors.selected_quests"
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
