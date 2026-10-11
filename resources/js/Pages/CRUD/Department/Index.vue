<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";

interface Props {
    items: Paginated<Department>;
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
        title="Departamentos"
        view="Department"
        :items="items"
        name="departments"
        :is-searchable="isSearchable"
    >
        <template #heading>Departamentos</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome do Departamento</Header>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
                <Header sort-by="priority">Prioridade</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="departments" :item="item">
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>
                <Cell class="font-mono text-xs text-neutral-300">
                    {{ item.priority }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
