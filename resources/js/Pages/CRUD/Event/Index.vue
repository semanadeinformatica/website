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

console.log(props.items);

const event_days = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.event_days.map((event_day) => [
            event_day.id,
            event_day.date + "",
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
                <Header sort-by="time_start">Hora de Início</Header>
                <Header sort-by="time_end">Hora de Fim</Header>
                <Header filter-by="event_day_id" :filter-values="event_days"
                    >Dia do evento</Header
                >
                <Header sort-by="capacity">Capacidade</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="events">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ item.time_start }} </Cell>
                <Cell>{{ item.time_end }} </Cell>
                <Cell>{{ event_days[item.event_day_id] }}</Cell>
                <Cell>{{ item.capacity ?? "N/A" }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
