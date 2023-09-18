<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";

interface Props {
    items: Paginated<Department>;
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
    <CRUDLayout title="Departments" :items="items" name="departments">
        <template #heading>Departamentos</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header filter-by="edition_id" :filter-values="editions"
                    >Edição</Header
                >
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="departments" :item="item">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ editions[item.edition_id] }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
