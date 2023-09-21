<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type CompetitionTeam from "@/Types/CompetitionTeam";
import type Competition from "@/Types/Competition";

interface Props {
    items: Paginated<CompetitionTeam>;
    with: {
        competitions: Competition[];
    };
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
</script>

<template>
    <CRUDLayout
        title="Competition Teams"
        :items="items"
        name="competitionTeams"
    >
        <template #heading>Equipas na Competição de Programação</template>

        <template #header>
            <HeaderRow>
                <Header>Edição</Header>
                <Header sort-by="name">Nome</Header>
                <Header>Nª Membros</Header>
                <Header sort-by="points">Pontos</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="competitionTeams" :item="item">
                <Cell>{{ editions[item.competition_id] }}</Cell>
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ item.participants?.length ?? "N/A" }}</Cell>
                <Cell>{{ item.points }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
