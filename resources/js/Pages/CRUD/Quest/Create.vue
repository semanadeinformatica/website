<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Company from "@/Types/Company";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    with: {
        editions: Edition[];
        companies: Company[];
    };
}

defineProps<Props>();

const form = useForm({
    name: "",
    points: "",
    category: "",
    requirement: "",
    edition_id: "",
});

const submit = () => {
    form.post(route("admin.quests.store"));
};
</script>

<template>
    <CardLayout title="Criar quest">
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

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
