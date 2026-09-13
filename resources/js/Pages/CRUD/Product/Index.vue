<script setup lang="ts">
import type Product from "@/Types/Product";
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
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
        title="Produtos"
        view="Product"
        :items="items"
        name="products"
        :is-searchable="isSearchable"
    >
        <template #heading>Produtos da Loja</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="price">Preço</Header>
                <Header sort-by="stock">Stock Disponível</Header>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="products">
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell>
                    <span
                        class="font-mono text-xs font-semibold text-neutral-200"
                    >
                        {{ item.price }}
                    </span>
                    <span class="ml-1 text-xs text-neutral-500">pts</span>
                </Cell>
                <Cell>
                    <span
                        v-if="item.stock === 0"
                        class="text-xs font-medium text-red-400"
                    >
                        Esgotado (0 un.)
                    </span>
                    <span v-else class="font-mono text-xs text-neutral-300">
                        {{ item.stock }}
                        <span class="font-sans text-neutral-500">un.</span>
                    </span>
                </Cell>
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
