<script setup lang="ts">
import type Speaker from "@/Types/Speaker";
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import type Event from "@/Types/Event";
import { computed } from "vue";

interface Props {
    items: Paginated<Speaker>;
    with: {
        events: Event[];
    };
}

const props = defineProps<Props>();

const events = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.events.map((event) => [event.id, event.name]),
    ),
);
</script>

<template>
    <CRUDLayout title="Speaker" :items="items" name="speakers">
        <template #heading>Speakers</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="event_id">Evento</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="speakers">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ events[item.event_id] }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
