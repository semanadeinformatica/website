<script setup lang="ts">
import type Product from "@/Types/Product";
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";

interface Props {
    items: Paginated<Product>;
}

defineProps<Props>();
</script>

<template>
    <CRUDLayout title="Product" :items="items" name="products">
        <template #heading>Produtos</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="price">Preço</Header>
                <Header sort-by="points">Stock</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="products">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ item.price }}</Cell>
                <Cell>{{ item.stock }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
