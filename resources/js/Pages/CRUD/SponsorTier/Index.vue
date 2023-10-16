<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
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
}

const props = defineProps<Props>();

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);
</script>

<template>
    <CRUDLayout title="Sponsor Tiers" :items="items" name="sponsorTiers">
        <template #heading
            ><span class="italic">Tier</span> de patrocínio</template
        >

        <template #header>
            <HeaderRow>
                <Header filter-by="edition_id" :filter-values="editions"
                    >Edição</Header
                >
                <Header sort-by="name">Nome</Header>

                <Header>Cor</Header>
                <Header>Pode ver CVs</Header>
                <Header>Pode ver Linkedin</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="sponsorTiers" :item="item">
                <Cell>{{ editions[item.edition_id] }}</Cell>
                <Cell>{{ item.name }}</Cell>
                <Cell>
                    <span
                        class="inline-block h-4 w-4 rounded-full"
                        :style="{ backgroundColor: item.color }"
                    ></span>
                </Cell>
                <Cell>{{ item.canSeeCV ? "✓" : "❌" }}</Cell>
                <Cell>{{ item.canSeeLinkedin ? "✓" : "❌" }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
