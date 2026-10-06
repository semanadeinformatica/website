<script setup lang="ts">
import type Event from "@/Types/Event";
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
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
            `${event_day.date} (${event_day.theme})`,
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

const formatTime = (time?: string) => {
    if (!time) return "-";
    return time.slice(0, 5);
};
</script>

<template>
    <CRUDView
        title="Eventos"
        view="Event"
        :items="items"
        name="events"
        :is-searchable="isSearchable"
    >
        <template #heading>Eventos</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="time_start">Horário</Header>
                <Header filter-by="event_day_id" :filter-values="event_days">
                    Dia do Evento
                </Header>
                <Header filter-by="event_type_id" :filter-values="event_types">
                    Tipo
                </Header>
                <Header sort-by="capacity">Capacidade</Header>
                <Header sort-by="location">Local</Header>
                <Header sort-by="enroll_in_site">Inscrição</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="events">
                <Cell class="font-medium text-white">
                    <span class="block max-w-xs truncate" :title="item.name">
                        {{ item.name }}
                    </span>
                </Cell>
                <Cell>
                    <span class="font-mono text-xs text-neutral-300">
                        {{ formatTime(item.time_start) }} –
                        {{ formatTime(item.time_end) }}
                    </span>
                </Cell>
                <Cell>
                    <span
                        class="block max-w-44 truncate text-xs text-neutral-400"
                    >
                        {{ event_days[item.event_day_id] ?? "-" }}
                    </span>
                </Cell>
                <Cell>
                    <span class="text-xs text-neutral-300 capitalize">
                        {{ event_types[item.event_type_id] ?? "-" }}
                    </span>
                </Cell>
                <Cell>
                    <span
                        v-if="item.capacity"
                        class="font-mono text-xs text-neutral-300"
                    >
                        {{ item.capacity }}
                    </span>
                    <span v-else class="text-xs text-neutral-500">
                        Ilimitada
                    </span>
                </Cell>
                <Cell class="text-xs text-neutral-300">
                    {{ item.location || "-" }}
                </Cell>
                <Cell>
                    <span
                        v-if="item.enroll_in_site"
                        class="inline-flex items-center text-xs font-medium text-emerald-400"
                    >
                        <span
                            class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-400"
                        />
                        Sim
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center text-xs text-neutral-500"
                    >
                        <span
                            class="mr-1.5 h-1.5 w-1.5 rounded-full bg-neutral-600"
                        />
                        Não
                    </span>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
