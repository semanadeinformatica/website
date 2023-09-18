<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
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
    edition_id: "",
    company_id: "",
    tier: "",
});

const submit = () => {
    form.post(route("admin.sponsors.store"));
};
</script>

<template>
    <CardLayout title="Atribuir Patrocínio">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                v-model="form.edition_id"
                type="select"
                required
                label="Edição"
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
                v-model="form.company_id"
                type="select"
                required
                label="Empresa"
            >
                <option
                    v-for="company in $props.with.companies"
                    :key="company.id"
                    :value="company.id"
                >
                    {{ company.user?.name ?? company.id }}
                </option>
            </TextInput>

            <TextInput
                v-model="form.tier"
                type="select"
                required
                label="Tipo de patrocínio"
            >
                <option value="platinum">Platina</option>
                <option value="gold">Ouro</option>
                <option value="silver">Prata</option>
            </TextInput>

            <PrimaryButton type="submit">Atribuir</PrimaryButton>
        </form>
    </CardLayout>
</template>
