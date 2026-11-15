<script setup lang="ts">
import type ArcadeGame from "@/Types/Arcade";
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import type Edition from "@/Types/Edition";

interface Props {
    items: Paginated<ArcadeGame>;
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
        title="Jogos do Arcade"
        view="ArcadeGames"
        :items="items"
        name="arcadeGames"
        :is-searchable="isSearchable"
    >
        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="slug">Slug</Header>
                <Header sort-by="is_active">Ativo</Header>
                <Header sort-by="min_wager">Aposta Mín.</Header>
                <Header sort-by="max_wager">Aposta Máx.</Header>
                <Header sort-by="daily_win_cap">Limite Diário</Header>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
            </HeaderRow>
        </template>
        <template #row="{ item }">
            <Row :item="item" name="arcadeGames">
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell
                    ><span class="font-mono text-xs text-neutral-400">
                        {{ item.slug }}
                    </span></Cell
                >
                <Cell>
                    <span
                        :class="
                            item.is_active ? 'text-green-400' : 'text-red-400'
                        "
                    >
                        {{ item.is_active ? "Ativo" : "Inativo" }}
                    </span>
                </Cell>
                <Cell>{{ item.min_wager }} pts</Cell>
                <Cell>{{ item.max_wager }} pts</Cell>
                <Cell>{{ item.daily_win_cap ?? "—" }}</Cell>
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "—" }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
