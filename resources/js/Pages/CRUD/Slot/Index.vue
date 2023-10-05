<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
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
}

const props = defineProps<Props>();

const quests = computed<number[]>(() =>
    props.with.quests.map((quest) => quest.id),
);
</script>

<template>
    <CRUDLayout title="Slots" :items="items" name="slots">
        <template #heading
            ><span class="italic">Slots</span> de tarefas</template
        >

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="total_quests">Número mínimo de tarefas</Header>
                <Header sort-by="points">Pontos</Header>
                <Header>Tarefas</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="slots">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ item.total_quests }}</Cell>
                <Cell>{{ item.points }}</Cell>
                <Cell>{{
                    item.quests?.filter((q: Quest) => quests.includes(q.id))
                        .length ?? "N/A"
                }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
