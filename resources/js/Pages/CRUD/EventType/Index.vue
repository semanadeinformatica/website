<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import type EventType from "@/Types/EventType";

interface Props {
    items: Paginated<EventType>;
}

defineProps<Props>();
</script>

<template>
    <CRUDLayout title="Event Types" :items="items" name="eventTypes">
        <template #heading>Event Types</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Tipo</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="eventTypes">
                <Cell>{{ item.name }} </Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
