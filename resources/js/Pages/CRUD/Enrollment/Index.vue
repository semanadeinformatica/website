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
    <CRUDLayout title="Enrollment" :items="items" name="enrollments">
        <template #heading>Inscrições</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="participant_id">Nome do Estudante</Header>
                <Header sort-by="edition_id">Nome da Edição</Header>
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
