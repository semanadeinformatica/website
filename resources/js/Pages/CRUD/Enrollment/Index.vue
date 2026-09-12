<script setup lang="ts">
import type Enrollment from "@/Types/Enrollment";
import type Paginated from "@/Types/Paginated";
import type Participant from "@/Types/Participant";
import type Edition from "@/Types/Edition";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
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
    <CRUDView
        title="Inscrições"
        view="Enrollment"
        :items="items"
        name="enrollments"
        :is-searchable="isSearchable"
    >
        <template #heading>Inscrições</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="participant_id" :filter-values="participants">
                    Participante
                </Header>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
                <Header sort-by="points">Pontos</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="enrollments">
                <Cell class="font-medium text-white">
                    {{ participants[item.participant_id] ?? `Participante #${item.participant_id}` }}
                </Cell>
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
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
