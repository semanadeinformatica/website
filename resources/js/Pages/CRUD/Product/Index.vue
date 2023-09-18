<script setup lang="ts">
import type Product from "@/Types/Product";
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type Edition from "@/Types/Edition";

interface Props {
    items: Paginated<Product>;
    with: {
        editions: Edition[];
    };
}

const props = defineProps<Props>();

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);
</script>

<template>
    <CRUDLayout title="Product" :items="items" name="products">
        <template #heading>Produtos</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="price">Preço</Header>
                <Header sort-by="stock">Stock</Header>
                <Header filter-by="edition_id" :filter-values="editions"
                    >Edição</Header
                >
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="products">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ item.price }}</Cell>
                <Cell>{{ item.stock }}</Cell>
                <Cell>{{ editions[item.edition_id] }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
