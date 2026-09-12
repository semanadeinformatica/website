<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import type EventType from "@/Types/EventType";

interface Props {
    items: Paginated<EventType>;
    isSearchable?: boolean;
}

defineProps<Props>();
</script>

<template>
    <CRUDView
        title="Tipos de Evento"
        view="EventType"
        :items="items"
        name="eventTypes"
        :is-searchable="isSearchable"
    >
        <template #heading>Tipos de Evento</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Tipo / Categoria</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="eventTypes">
                <Cell class="font-medium text-white capitalize">
                    {{ item.name }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
