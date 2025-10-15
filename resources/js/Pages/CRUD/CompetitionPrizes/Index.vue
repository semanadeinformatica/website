<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type Competition from "@/Types/Competition";
import type { CompetitionPrizes } from "@/Types/Competition";

interface Props {
    items: Paginated<CompetitionPrizes>;
    with: {
        competitions: Competition[];
    };
    isSearchable?: boolean;
}

const props = defineProps<Props>();

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.competitions.map((competition) => [
            competition.id,
            competition.edition?.name ?? competition.id.toString(),
        ]),
    ),
);

const competitionNames = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.competitions.map((competition) => [
            competition.id,
            competition.name,
        ]),
    ),
);
</script>

<template>
    <CRUDLayout
        title="Competition Prizes"
        :items="items"
        name="competitionPrizes"
        :is-searchable="isSearchable"
    >
        <template #heading>Prémios na Competição de Programação</template>

        <template #header>
            <HeaderRow>
                <Header>Edição</Header>
                <Header sort-by="place">Lugar</Header>
                <Header>Imagem</Header>
                <Header>Competição</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="competitionPrizes" :item="item">
                <Cell>{{ editions[item.competition_id] }}</Cell>
                <Cell>{{ item.place }}</Cell>
                <Cell>{{ item.prize_picture?.name ?? "N/A" }}</Cell>
                <Cell>{{ competitionNames[item.competition_id] }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>

