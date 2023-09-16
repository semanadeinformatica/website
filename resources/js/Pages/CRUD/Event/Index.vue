<script setup lang="ts">
import type Event from "@/Types/Event";
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type EventDay from "@/Types/EventDay";

interface Props {
    items: Paginated<Event>;
    with: {
        event_days: EventDay[];
    };
}

const props = defineProps<Props>();

const event_days = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.event_days.map((event_day) => [
            event_day.id,
            event_day.date.getDate() + "",
        ]),
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
                <Header>Edição</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="events">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ new Date(item.time_start).toLocaleString() }} </Cell>
                <Cell>{{ new Date(item.time_end).toLocaleString() }} </Cell>
                <Cell>{{ event_days[item.event_day_id] }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
