<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Sponsor from "@/Types/Sponsor";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type Stand from "@/Types/Stand";
import type EventDay from "@/Types/EventDay";

interface Props {
    items: Paginated<Stand>;
    with: {
        eventDays: EventDay[];
        sponsors: Sponsor[];
    };
}

const props = defineProps<Props>();

const eventDays = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.eventDays.map((eventDay) => [
            eventDay.id,
            eventDay.date.toString(),
        ]),
    ),
);

const sponsors = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.sponsors.map((sponsor) => [
            sponsor.id,
            sponsor.company.user?.name ?? "",
        ]),
    ),
);
</script>

<template>
    <CRUDLayout title="Stands" :items="items" name="stands">
        <template #heading>Bancas</template>

        <template #header>
            <HeaderRow>
                <Header>Empresa</Header>
                <Header sort-by="name">Data</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="sponsors" :item="item">
                <Cell>{{ sponsors[item.sponsor_id] }}</Cell>
                <Cell>{{ eventDays[item.event_day_id] }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
