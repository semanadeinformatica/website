<script setup lang="ts" generic="T extends Model">
import Table from "@/Components/CRUD/Table.vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import type Model from "@/Types/Model";
import type Paginated from "@/Types/Paginated";
import AppLayout from "./AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import route from "ziggy-js";
import TextInput from "@/Components/TextInput.vue";
import { ref, watch } from "vue";

defineProps<{
    items: Paginated<T>;
    title: string;
    name: string;
}>();

const query = ref(new URLSearchParams(location.search).get("query") ?? "");

watch(query, (query) => {
    const ziggy = route();

    router.replace(route(ziggy.current() ?? "#", { ...ziggy.params, query }), {
        preserveState: true,
        preserveScroll: true,
    });
});
</script>

<template>
    <AppLayout :title="title">
        <div class="flex flex-col items-stretch gap-4 bg-2023-bg p-12">
            <header
                class="flex flex-row flex-wrap items-center justify-between gap-4"
            >
                <h2 class="mr-auto text-2xl"><slot name="heading"></slot></h2>

                <TextInput v-model="query" label="Pesquisar" type="search" />

                <Link :href="route(`admin.${name}.create`)">Novo</Link>
            </header>

            <Table v-if="items.data.length > 0" :items="items.data">
                <template #header>
                    <slot name="header"></slot>
                </template>

                <template #row="{ item }">
                    <slot name="row" :item="item"></slot>
                </template>
            </Table>

            <div v-else class="text-xl">Nenhum item encontrado</div>

            <PaginationLinks :links="items.links" />
        </div>
    </AppLayout>
</template>
