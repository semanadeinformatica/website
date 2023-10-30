<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Sponsor from "@/Types/Sponsor";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import type Edition from "@/Types/Edition";
import type Company from "@/Types/Company";
import { computed } from "vue";
import type SponsorTier from "@/Types/SponsorTier";

interface Props {
    items: Paginated<Sponsor>;
    with: {
        editions: Edition[];
        companies: Company[];
        tiers: SponsorTier[];
    };
    isSearchable?: boolean;
}

const props = defineProps<Props>();

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);

const companies = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.companies.map((company) => [
            company.id,
            company.user?.name ?? "",
        ]),
    ),
);

const tiers = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.tiers.map((tier) => [
            tier.id,
            `${editions.value[tier.edition_id]} - ${tier.name}`,
        ]),
    ),
);
</script>

<template>
    <CRUDLayout
        title="Sponsor"
        :items="items"
        name="sponsors"
        :is-searchable="isSearchable"
    >
        <template #heading>Patrocínios</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="edition_id" :filter-values="editions"
                    >Edição</Header
                >
                <Header filter-by="company_id" :filter-values="companies"
                    >Empresa</Header
                >
                <Header filter-by="tier" :filter-values="tiers"
                    >Tipo de patrocínio</Header
                >
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="sponsors" :item="item">
                <Cell>{{ editions[item.edition_id] }}</Cell>
                <Cell>{{ companies[item.company_id] }}</Cell>
                <Cell>{{ tiers[item.sponsor_tier_id] }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
