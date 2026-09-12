<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
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
</script>

<template>
    <CRUDView
        title="Equipas da Competição"
        view="CompetitionTeam"
        :items="items"
        name="competitionTeams"
        :is-searchable="isSearchable"
    >
        <template #heading>Equipas da Competição</template>

        <template #header>
            <HeaderRow>
                <Header>Edição</Header>
                <Header sort-by="name">Nome da Equipa</Header>
                <Header>Nº Membros</Header>
                <Header sort-by="points">Pontos</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="competitionTeams" :item="item">
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.competition_id] ?? "-" }}
                </Cell>
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell class="font-mono text-xs text-neutral-300">
                    {{ item.members?.length ?? 0 }}
                </Cell>
                <Cell>
                    <span class="font-mono text-xs font-semibold text-neutral-200">
                        {{ item.points }}
                    </span>
                    <span class="ml-1 text-xs text-neutral-500">pts</span>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
