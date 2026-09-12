<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type Slot from "@/Types/Slot";
import type Quest from "@/Types/Quest";

interface Props {
    items: Paginated<Slot>;
    with: {
        quests: Quest[];
    };
    isSearchable?: boolean;
}

const props = defineProps<Props>();

const quests = computed<number[]>(() =>
    props.with.quests.map((quest) => quest.id),
);
</script>

<template>
    <CRUDView
        title="Encaixes de Tarefas"
        view="Slot"
        :items="items"
        name="slots"
        :is-searchable="isSearchable"
    >
        <template #heading>Encaixes de Tarefas (Slots)</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome do Encaixe</Header>
                <Header sort-by="total_quests">Mínimo de Tarefas</Header>
                <Header sort-by="points">Pontuação</Header>
                <Header>Tarefas Associadas</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="slots">
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell>
                    <span class="font-mono text-xs text-neutral-300">
                        {{ item.total_quests }}
                    </span>
                </Cell>
                <Cell>
                    <span class="font-mono text-xs font-semibold text-neutral-200">
                        {{ item.points }}
                    </span>
                    <span class="ml-1 text-xs text-neutral-500">pts</span>
                </Cell>
                <Cell>
                    <span class="font-mono text-xs text-neutral-400">
                        {{
                            item.quests?.filter((q: Quest) => quests.includes(q.id))
                                .length ?? 0
                        }}
                    </span>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
