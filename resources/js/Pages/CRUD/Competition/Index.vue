<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Edition from "@/Types/Edition";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type Competition from "@/Types/Competition";
import { ExternalLink } from "@lucide/vue";

interface Props {
    items: Paginated<Competition>;
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
        title="Competições"
        view="Competition"
        :items="items"
        name="competitions"
        :is-searchable="isSearchable"
    >
        <template #heading>Competições</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="theme">Tema</Header>
                <Header>Descrição</Header>
                <Header>Registo</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="competitions" :item="item">
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell class="text-xs text-neutral-300">{{ item.theme }}</Cell>
                <Cell>
                    <span
                        class="block max-w-xs truncate text-xs text-neutral-400"
                        :title="item.description"
                    >
                        {{ item.description || "-" }}
                    </span>
                </Cell>
                <Cell>
                    <a
                        v-if="item.registration_link"
                        :href="item.registration_link"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-xs font-medium text-neutral-300 transition-colors hover:text-white hover:underline"
                    >
                        <span>Registar</span>
                        <ExternalLink :size="11" />
                    </a>
                    <span v-else class="text-xs text-neutral-600">—</span>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
