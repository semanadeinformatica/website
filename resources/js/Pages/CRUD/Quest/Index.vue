<script setup lang="ts">
import type Quest from "@/Types/Quest";
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type Edition from "@/Types/Edition";

interface Props {
    items: Paginated<Quest>;
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
        title="Tarefas"
        view="Quest"
        :items="items"
        name="quests"
        :is-searchable="isSearchable"
    >
        <template #heading>Tarefas & Missões</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome da Tarefa</Header>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="quests">
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
