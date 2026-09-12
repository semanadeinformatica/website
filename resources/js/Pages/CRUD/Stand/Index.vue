<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Sponsor from "@/Types/Sponsor";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
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
    isSearchable?: boolean;
}

const props = defineProps<Props>();

const eventDays = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.eventDays.map((eventDay) => [
            eventDay.id,
            `${eventDay.date} (${eventDay.theme})`,
        ]),
    ),
);

const sponsors = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.sponsors.map((sponsor) => [
            sponsor.id,
            sponsor.company?.user?.name ?? `Patrocinador #${sponsor.id}`,
        ]),
    ),
);
</script>

<template>
    <CRUDView
        title="Bancas"
        view="Stand"
        :items="items"
        name="stands"
        :is-searchable="isSearchable"
    >
        <template #heading>Bancas de Empresas</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="sponsor_id" :filter-values="sponsors">
                    Empresa
                </Header>
                <Header filter-by="event_day_id" :filter-values="eventDays">
                    Dia do Evento
                </Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="stands" :item="item">
                <Cell class="font-medium text-white">
                    {{ sponsors[item.sponsor_id] ?? `Empresa #${item.sponsor_id}` }}
                </Cell>
                <Cell class="text-xs text-neutral-400">
                    {{ eventDays[item.event_day_id] ?? "-" }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
