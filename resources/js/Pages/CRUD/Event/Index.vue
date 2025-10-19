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
import type EventType from "@/Types/EventType";

interface Props {
    items: Paginated<Event>;
    with: {
        event_days: EventDay[];
        event_types: EventType[];
    };
    isSearchable?: boolean;
}

const props = defineProps<Props>();

const event_days = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.event_days.map((event_day) => [
            event_day.id,
            event_day.date + "",
        ]),
    ),
);

const event_types = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.event_types.map((event_type) => [
            event_type.id,
            event_type.name,
        ]),
    ),
);
</script>

<template>
    <CRUDLayout
        title="Event"
        :items="items"
        name="events"
        :is-searchable="isSearchable"
    >
        <template #heading>Events</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="time_start">Hora de Início</Header>
                <Header sort-by="time_end">Hora de Fim</Header>
                <Header filter-by="event_day_id" :filter-values="event_days"
                    >Dia do evento</Header
                >
                <Header filter-by="event_type_id" :filter-values="event_types"
                    >Tipo do evento</Header
                >
                <Header sort-by="capacity">Capacidade</Header>
                <Header sort-by="capacity">Local</Header>
                <Header sort-by="capacity">Inscrição no site</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="events">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ item.time_start }} </Cell>
                <Cell>{{ item.time_end }} </Cell>
                <Cell>{{ event_days[item.event_day_id] }}</Cell>
                <Cell>{{ event_types[item.event_type_id] }}</Cell>
                <Cell>{{ item.capacity ?? "N/A" }}</Cell>
                <Cell>{{ item.location }}</Cell>
                <Cell>{{ item.enroll_in_site ? "Sim" : "Não" }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
