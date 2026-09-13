<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import type Edition from "@/Types/Edition";
import { computed } from "vue";
import type SponsorTier from "@/Types/SponsorTier";

interface Props {
    items: Paginated<SponsorTier>;
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
        title="Níveis de Patrocínio"
        view="SponsorTier"
        :items="items"
        name="sponsorTiers"
        :is-searchable="isSearchable"
    >
        <template #heading>Níveis de Patrocínio</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
                <Header sort-by="name">Nome</Header>
                <Header>Cor</Header>
                <Header>Ver CVs</Header>
                <Header>Ver LinkedIn</Header>
                <Header>Todos Participantes</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="sponsorTiers" :item="item">
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-block h-3.5 w-3.5 rounded-full border border-white/20"
                            :style="{ backgroundColor: item.color }"
                        />
                        <span class="font-mono text-xs text-neutral-400">
                            {{ item.color }}
                        </span>
                    </div>
                </Cell>
                <Cell>
                    <span
                        v-if="item.canSeeCV"
                        class="inline-flex items-center text-xs font-medium text-emerald-400"
                    >
                        <span
                            class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-400"
                        />
                        Sim
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center text-xs text-neutral-500"
                    >
                        <span
                            class="mr-1.5 h-1.5 w-1.5 rounded-full bg-neutral-600"
                        />
                        Não
                    </span>
                </Cell>
                <Cell>
                    <span
                        v-if="item.canSeeLinkedin"
                        class="inline-flex items-center text-xs font-medium text-emerald-400"
                    >
                        <span
                            class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-400"
                        />
                        Sim
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center text-xs text-neutral-500"
                    >
                        <span
                            class="mr-1.5 h-1.5 w-1.5 rounded-full bg-neutral-600"
                        />
                        Não
                    </span>
                </Cell>
                <Cell>
                    <span
                        v-if="item.canSeeAll"
                        class="inline-flex items-center text-xs font-medium text-emerald-400"
                    >
                        <span
                            class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-400"
                        />
                        Sim
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center text-xs text-neutral-500"
                    >
                        <span
                            class="mr-1.5 h-1.5 w-1.5 rounded-full bg-neutral-600"
                        />
                        Não
                    </span>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
