<script setup lang="ts">
import type FinalPrize from "@/Types/FinalPrize";
import type Edition from "@/Types/Edition";
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";
import { Gift } from "@lucide/vue";

interface Props {
    items: Paginated<FinalPrize>;
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

const formatPlace = (place: number | string) => `${place}º Lugar`;
</script>

<template>
    <CRUDView
        title="Prémios Finais"
        view="FinalPrizes"
        :items="items"
        name="finalPrizes"
        :is-searchable="isSearchable"
    >
        <template #heading>Prémios Finais</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
                <Header sort-by="place">Classificação</Header>
                <Header sort-by="title">Título</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="finalPrizes" :item="item">
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>
                <Cell>
                    <span
                        class="text-xs font-semibold"
                        :class="
                            item.place === 1
                                ? 'text-amber-400'
                                : item.place === 2
                                  ? 'text-slate-300'
                                  : 'text-amber-600'
                        "
                    >
                        {{ formatPlace(item.place) }}
                    </span>
                </Cell>
                <Cell>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-white/10 bg-neutral-900"
                        >
                            <img
                                v-if="item.final_prize_image_url"
                                :src="item.final_prize_image_url"
                                :alt="item.title"
                                class="h-full w-full object-contain p-1"
                            />
                            <Gift v-else :size="16" class="text-neutral-600" />
                        </div>
                        <span class="font-medium text-white">
                            {{ item.title }}
                        </span>
                    </div>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
