<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Edition from "@/Types/Edition";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type Competition from "@/Types/Competition";

interface Props {
    items: Paginated<Competition>;
    with: {
        editions: Edition[];
    };
}

const props = defineProps<Props>();

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);
</script>

<template>
    <CRUDLayout title="Competition" :items="items" name="competitions">
        <template #heading>Competições</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="edition_id" :filter-values="editions"
                    >Edição</Header
                >
                <Header sort-by="name">Nome</Header>
                <Header sort-by="theme">Tema</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="competitions" :item="item">
                <Cell>{{ editions[item.edition_id] }}</Cell>
                <Cell>{{ item.theme }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
