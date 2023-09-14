<script setup lang="ts">
import type Paginated from '@/Types/Paginated';
import type Sponsor from '@/Types/Sponsor';
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import type Edition from '@/Types/Edition';
import type Company from '@/Types/Company';
import { computed } from 'vue';

interface Props {
    items: Paginated<Sponsor>;
    with: {
        editions: Edition[];
        companies: Company[];
    }
};

const props = defineProps<Props>();

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);

const companies = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.companies.map((company) => [company.id, company.user?.name ?? ""]),
    ),
);

</script>

<template>
    <CRUDLayout title="Sponsor" :items="items" name="sponsors">
        <template #heading>Patrocínios</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Edição</Header>
                <Header>Empresa</Header>
                <Header>Tipo de patrocínio</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="sponsors" :item="item">
                <Cell>{{ editions[item.edition_id] }}</Cell>
                <Cell>{{ companies[item.company_id] }}</Cell>
                <Cell>{{ item.tier }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>

