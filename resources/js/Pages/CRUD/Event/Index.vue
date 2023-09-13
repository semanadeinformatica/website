<script setup lang="ts">
import type Event from "@/Types/Event";
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import type Edition from "@/Types/Edition";
import { computed } from "vue";

interface Props {
    items: Paginated<Event>;
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
    <CRUDLayout title="Event" :items="items" name="events">
        <template #heading>Events</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="date_start">Data de Início</Header>
                <Header sort-by="date_end">Data de Fim</Header>
                <Header sort-by="edition_id">Edição</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="events">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ new Date(item.date_start).toLocaleString() }} </Cell>
                <Cell>{{ new Date(item.date_end).toLocaleString() }} </Cell>
                <Cell>{{ editions[item.edition_id] }} </Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
