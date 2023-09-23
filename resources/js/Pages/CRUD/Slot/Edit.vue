<script setup lang="ts">
import type Quest from "@/Types/Quest";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import CardLayout from "@/Layouts/CardLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import type Slot from "@/Types/Slot";

interface Props {
    item: Slot;
    with: {
        quests: Quest[];
    };
}

const { item: slot } = defineProps<Props>();

const form = useForm({
    total_quests: slot.total_quests.toString(),
    points: slot.points.toString(),
    quests: slot.quests?.map((q) => q.id.toString()) ?? [],
});

const submit = () => {
    form.put(route("admin.slots.update", slot));
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
