<script setup lang="ts">
import TextInput from "@/Components/Form/TextInput.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Company from "@/Types/Company";
import type Edition from "@/Types/Edition";
import type Sponsor from "@/Types/Sponsor";
import type SponsorTier from "@/Types/SponsorTier";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    item: Sponsor;
    with: {
        editions: Edition[];
        companies: Company[];
        tiers: SponsorTier[];
    };
}

const { item: sponsor } = defineProps<Props>();

const form = useForm({
    edition_id: sponsor.edition_id.toString(),
    company_id: sponsor.company_id.toString(),
    sponsor_tier_id: sponsor.sponsor_tier_id.toString(),
});

const submit = () => {
    form.put(route("admin.sponsors.update", { sponsor: sponsor.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar Patrocínio"
        name="sponsors"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
        <TextInput
            v-model="form.edition_id"
            type="select"
            required
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
            v-model="form.company_id"
            type="select"
            required
            label="Empresa"
            :error-message="form.errors.company_id"
        >
            <option
                v-for="company in $props.with.companies"
                :key="company.id"
                :value="company.id"
            >
                {{ company.user?.name }}
            </option>
        </TextInput>

        <TextInput
            v-model="form.sponsor_tier_id"
            type="select"
            required
            label="Tipo de patrocínio"
            :error-message="form.errors.sponsor_tier_id"
        >
            <option
                v-for="tier in $props.with.tiers"
                :key="tier.id"
                :value="tier.id"
            >
                {{ tier.name }}
            </option>
        </TextInput>
    </CRUDModal>
</template>
