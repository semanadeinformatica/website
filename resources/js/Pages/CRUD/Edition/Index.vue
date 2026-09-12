<script setup lang="ts">
import type Edition from "@/Types/Edition";
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";

interface Props {
    items: Paginated<Edition>;
    isSearchable?: boolean;
}

defineProps<Props>();
</script>

<template>
    <CRUDView
        title="Edições"
        view="Edition"
        :items="items"
        name="editions"
        :is-searchable="isSearchable"
    >
        <template #heading>Edições do Evento</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome da Edição</Header>
                <Header sort-by="year">Ano</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="editions">
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell>
                    <span class="font-mono text-xs text-neutral-400">
                        {{ item.year }}
                    </span>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
