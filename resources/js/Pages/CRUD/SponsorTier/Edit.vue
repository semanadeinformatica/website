<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import Checkbox from "@/Components/Checkbox.vue";
import type SponsorTier from "@/Types/SponsorTier";

interface Props {
    with: {
        editions: Edition[];
    };
    item: SponsorTier;
}

const { item: tier } = defineProps<Props>();

const form = useForm({
    edition_id: tier.edition_id.toString(),
    name: tier.name,
    color: tier.color,
    rank: tier.rank.toString(),
    canSeeCV: tier.canSeeCV,
    canSeeLinkedin: tier.canSeeLinkedin,
});

const submit = () => {
    form.put(route("admin.sponsorTiers.update", tier));
};
</script>

<template>
    <CardLayout title="Atribuir Patrocínio">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                v-model="form.edition_id"
                type="select"
                required
                autofocus
                label="Edição"
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

            <TextInput
                id="name"
                v-model="form.name"
                label="Nome"
                type="text"
                required
                autocomplete="name"
                :error-message="form.errors.name"
            />

            <TextInput
                id="color"
                v-model="form.color"
                label="Cor"
                type="color"
                required
                :error-message="form.errors.color"
            />

            <TextInput
                id="rank"
                v-model="form.rank"
                label="Posição"
                type="number"
                required
                :error-message="form.errors.rank"
            />

            <label for="canSeeCV" class="flex flex-row items-center gap-6">
                Consegue ver os CVs dos participantes?
                <Checkbox id="canSeeCV" v-model:checked="form.canSeeCV" />
            </label>

            <label
                for="canSeeLinkedin"
                class="flex flex-row items-center gap-6"
            >
                Consegue ver o LinkedIn dos participantes?
                <Checkbox
                    id="canSeeLinkedin"
                    v-model:checked="form.canSeeLinkedin"
                />
            </label>

            <PrimaryButton type="submit">Atribuir</PrimaryButton>
        </form>
    </CardLayout>
</template>
