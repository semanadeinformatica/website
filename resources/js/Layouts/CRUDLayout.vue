<script setup lang="ts" generic="T extends Model">
import Table from "@/Components/CRUD/Table.vue";
import PaginationLinks from "@/Components/UI/PaginationLinks.vue";
import type Model from "@/Types/Model";
import type Paginated from "@/Types/Paginated";
import AdminLayout from "./AdminLayout.vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import TextInput from "@/Components/Form/TextInput.vue";
import useSearch from "@/Composables/useSearch";

defineProps<{
    items: Paginated<T>;
    title: string;
    name: string;
    isSearchable?: boolean;
}>();

const query = useSearch("query", ["items"]);
</script>

<template>
    <AdminLayout :title="title">
        <div
            class="bg-2023-bg flex min-h-screen flex-col items-stretch gap-4 p-12"
        >
            <header
                class="flex flex-row flex-wrap items-center justify-between gap-4"
            >
                <h2 class="mr-auto text-2xl"><slot name="heading"></slot></h2>

                <TextInput
                    v-if="isSearchable"
                    v-model="query"
                    label="Pesquisar"
                    type="search"
                />

                <Link :href="route(`admin.${name}.create`)">Novo</Link>
            </header>

            <Table :items="items.data">
                <template #header>
                    <slot name="header"></slot>
                </template>

                <template #row="{ item }">
                    <slot name="row" :item="item"></slot>
                </template>
            </Table>

            <PaginationLinks
                :links="items.links"
                :link-props="{
                    preserveState: true,
                    preserveScroll: true,
                    only: ['items'],
                }"
            />
        </div>
    </AdminLayout>
</template>
