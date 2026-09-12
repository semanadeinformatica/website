<script setup lang="ts" generic="T extends Model">
import { computed, type Component } from "vue";
import { usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Table from "@/Components/CRUD/Table.vue";
import PaginationLinks from "@/Components/UI/PaginationLinks.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import useSearch from "@/Composables/useSearch";
import type Model from "@/Types/Model";
import type Paginated from "@/Types/Paginated";
import { route } from "ziggy-js";
import { Plus, Search, X } from "@lucide/vue";

interface Props {
    title: string;
    name: string;
    view?: string;
    items: Paginated<T>;
    isSearchable?: boolean;
    canCreate?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    view: "",
    isSearchable: false,
    canCreate: true,
});

defineSlots<{
    heading?: () => unknown;
    actions?: () => unknown;
    header?: () => unknown;
    row?: (props: { item: T }) => unknown;
}>();

const search = useSearch();
const page = usePage();

const createModules = import.meta.glob<Component>(
    "../../Pages/CRUD/*/Create.vue",
    { eager: true, import: "default" },
);
const editModules = import.meta.glob<Component>("../../Pages/CRUD/*/Edit.vue", {
    eager: true,
    import: "default",
});

const currentView = computed(() => {
    if (props.view) return props.view;
    if (typeof page.props.view === "string") return page.props.view;
    return "";
});

const modalAction = computed(
    () => page.props.modal as "create" | "edit" | undefined,
);
const modalItem = computed(() => page.props.item as Model | undefined);
const modalWith = computed(
    () => page.props.with as Record<string, unknown> | undefined,
);

const activeModalComponent = computed(() => {
    if (!modalAction.value || !currentView.value) return null;
    if (modalAction.value === "create") {
        return (
            createModules[`../../Pages/CRUD/${currentView.value}/Create.vue`] ??
            null
        );
    }
    if (modalAction.value === "edit") {
        return (
            editModules[`../../Pages/CRUD/${currentView.value}/Edit.vue`] ??
            null
        );
    }
    return null;
});
</script>

<template>
    <AdminLayout :title="title">
        <template #header-title>
            <slot name="heading">
                <h1
                    class="text-base font-bold tracking-tight text-white sm:text-lg"
                >
                    {{ title }}
                </h1>
            </slot>
        </template>

        <template #header-actions>
            <slot name="actions">
                <!-- Pill Search Input -->
                <div
                    v-if="isSearchable"
                    class="relative flex flex-1 items-center sm:flex-initial"
                >
                    <Search
                        :size="15"
                        class="pointer-events-none absolute left-3 z-10 text-neutral-300"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Pesquisar..."
                        class="pill-container h-8 w-full pr-7 pl-8.5 text-xs text-white placeholder-neutral-400 focus:outline-none sm:w-60"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="absolute right-2.5 z-10 cursor-pointer text-neutral-400 transition-colors hover:text-white"
                        aria-label="Limpar pesquisa"
                        @click="search = ''"
                    >
                        <X :size="13" />
                    </button>
                </div>

                <!-- "Novo" Pill Button -->
                <PrimaryButton
                    v-if="canCreate"
                    :href="route(`admin.${name}.create`)"
                    color="pill"
                    padding="px-3.5 py-1.5"
                    text-size="text-xs"
                    class="flex shrink-0 items-center gap-1.5"
                >
                    <Plus :size="14" />
                    <span>Novo</span>
                </PrimaryButton>
            </slot>
        </template>

        <!-- Main View: Table & Pagination -->
        <div class="flex flex-col gap-6">
            <Table :items="items.data">
                <template #header>
                    <slot name="header" />
                </template>
                <template #row="{ item }">
                    <slot name="row" :item="item" />
                </template>
            </Table>

            <PaginationLinks :links="items.links" />
        </div>

        <!-- Dynamic CRUD Modal rendered OVER the table -->
        <component
            :is="activeModalComponent"
            v-if="activeModalComponent"
            :item="modalItem"
            :with="modalWith"
        />
    </AdminLayout>
</template>
