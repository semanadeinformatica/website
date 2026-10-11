<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
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

const formatPlace = (place: number | string) => {
    return `${place}º Lugar`;
};
</script>

<template>
    <CRUDView
        title="Prémios da Competição"
        view="CompetitionPrizes"
        :items="items"
        name="competitionPrizes"
        :is-searchable="isSearchable"
    >
        <template #heading>Prémios da Competição</template>

        <template #header>
            <HeaderRow>
                <Header>Edição</Header>
                <Header sort-by="place">Classificação</Header>
                <Header>Competição</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="competitionPrizes" :item="item">
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.competition_id] ?? "-" }}
                </Cell>
                <Cell>
                    <span
                        class="text-xs font-semibold"
                        :class="
                            item.place === 1
                                ? 'text-amber-400'
                                : item.place === 2
                                  ? 'text-slate-300'
                                  : item.place === 3
                                    ? 'text-amber-600'
                                    : 'text-neutral-300'
                        "
                    >
                        {{ formatPlace(item.place) }}
                    </span>
                </Cell>
                <Cell class="font-medium text-white">
                    {{ competitionNames[item.competition_id] ?? "-" }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
