<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type EventDay from "@/Types/EventDay";
import type Edition from "@/Types/Edition";

interface Props {
    items: Paginated<EventDay>;
    with: {
        editions: Edition[];
    };
    isSearchable?: boolean;
}

const props = defineProps<Props>();

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);
</script>

<template>
    <CRUDView
        title="Dias de Evento"
        view="EventDay"
        :items="items"
        name="eventDays"
        :is-searchable="isSearchable"
    >
        <template #heading>Dias de Evento</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
                <Header sort-by="theme">Tema Principal</Header>
                <Header sort-by="date">Data</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="eventDays">
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>
                <Cell class="font-medium text-white">{{ item.theme }}</Cell>
                <Cell>
                    <span class="font-mono text-xs text-neutral-300">
                        {{ item.date }}
                    </span>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
