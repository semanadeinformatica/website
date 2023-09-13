<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Company from "@/Types/Company";
import type Edition from "@/Types/Edition";
import type Quest from "@/Types/Quest";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Quest;
    with: {
        editions: Edition[];
        companies: Company[];
    };
}

const { item: quest } = defineProps<Props>();

const form = useForm({
    name: quest.name,
    points: quest.points.toString(),
    category: quest.category.toLowerCase(),
    requirement:
        (quest.requirement_type.split("\\").pop() ?? "").toLowerCase() +
        ";" +
        quest.requirement_id.toString(),
    edition_id: quest.edition_id,
});

const submit = () => {
    form.put(route("admin.quests.update", quest));
};
</script>

<template>
    <CardLayout title="Editar quest">
        <form class="contents" @submit.prevent="submit">
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

            <select v-model="form.category" required class="self-stretch">
                <option value="" disabled selected hidden>Categoria</option>
                <option value="company">Empresa</option>
                <option value="talk">Palestra</option>
                <option value="workshop">Workshop</option>
                <option value="milestone">Milestone</option>
                <option value="teambuilding">Team Building</option>
            </select>

            <select v-model="form.requirement" required class="self-stretch">
                <option value="" disabled selected hidden>Requisito</option>
                <option value="" disabled>Empresa</option>
                <option
                    v-for="company in $props.with.companies"
                    :key="'company;' + company.id"
                    :value="'company;' + company.id"
                >
                    {{ company.user?.name ?? company.id }}
                </option>
            </select>

            <select v-model="form.edition_id" required class="self-stretch">
                <option value="" disabled selected hidden>Edição</option>
                <option
                    v-for="edition in $props.with.editions"
                    :key="edition.id"
                    :value="edition.id"
                >
                    {{ edition.name }}
                </option>
            </select>

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>