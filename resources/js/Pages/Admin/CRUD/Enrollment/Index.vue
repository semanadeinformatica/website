<script setup lang="ts">
import type Enrollment from "@/Types/Enrollment";
import type Paginated from "@/Types/Paginated";
import type Participant from "@/Types/Participant";
import type Edition from "@/Types/Edition";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";

interface Props {
    items: Paginated<Enrollment>;
    with: {
        participants: Participant[];
        editions: Edition[];
    };
    isSearchable?: boolean;
}

const props = defineProps<Props>();

const participants = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.participants.map((participant) => [
            participant.id,
            participant.user?.name ?? "",
        ]),
    ),
);

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);
</script>

<template>
    <CRUDLayout
        title="Enrollment"
        :items="items"
        name="enrollments"
        :is-searchable="isSearchable"
    >
        <template #heading>Inscrições</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="participant_id" :filter-values="participants"
                    >Nome do Estudante</Header
                >
                <Header filter-by="edition_id" :filter-values="editions"
                    >Nome da Edição</Header
                >
                <Header sort-by="points">Pontos</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="enrollments">
                <Cell>{{ participants[item.participant_id] }}</Cell>
                <Cell>{{ editions[item.edition_id] }}</Cell>
                <Cell>{{ item.points }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
