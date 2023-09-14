<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Company from "@/Types/Company";
import type Edition from "@/Types/Edition";
import type Sponsor from "@/Types/Sponsor";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Sponsor;
    with: {
        editions: Edition[];
        companies: Company[];
    };
}

const { item: sponsor } = defineProps<Props>();

const form = useForm({
    edition_id: sponsor.edition_id,
    company_id: sponsor.company_id,
    tier: sponsor.tier.toLowerCase(),
});

const submit = () => {
    form.put(route("admin.sponsors.update", sponsor));
};
</script>

<template>
    <CardLayout title="Editar Patrocínio">
        <form class="contents" @submit.prevent="submit">
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

            <select v-model="form.company_id" required class="self-stretch">
                <option value="" disabled selected hidden>Empresa</option>
                <option
                    v-for="company in $props.with.companies"
                    :key="company.id"
                    :value="company.id"
                >
                    {{ company.user?.name }}
                </option>
            </select>

            <select v-model="form.tier" required class="self-stretch">
                <option value="" disabled selected hidden>
                    Tipo de patrocínio
                </option>
                <option value="platinum">Platina</option>
                <option value="gold">Ouro</option>
                <option value="silver">Prata</option>
            </select>

            <PrimaryButton type="submit">Atribuir</PrimaryButton>
        </form>
    </CardLayout>
</template>
