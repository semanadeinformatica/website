<script setup lang="ts">
import type Edition from "@/Types/Edition";
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";

interface Props {
    items: Paginated<Edition>;
}

defineProps<Props>();
</script>

<template>
    <CRUDLayout title="Edition" :items="items" name="editions">
        <template #heading>Edições</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="editions">
                <Cell>{{ item.name }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
