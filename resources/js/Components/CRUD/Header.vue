<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import { ArrowUpDown, ArrowUp, ArrowDown, Filter, Check } from "@lucide/vue";

interface Props {
    sortBy?: string;
    filterBy?: string;
    filterValues?: Record<string | number, string>;
}

const props = defineProps<Props>();

const filterMenuOpen = ref(false);
const filterContainerRef = ref<HTMLElement | null>(null);

const handleClickOutside = (e: MouseEvent) => {
    if (
        filterContainerRef.value &&
        !filterContainerRef.value.contains(e.target as Node)
    ) {
        filterMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

const currentSortBy = computed(() => {
    if (typeof window === "undefined") return null;
    return new URLSearchParams(window.location.search).get("sort_by");
});

const currentSortDir = computed(() => {
    if (typeof window === "undefined") return "asc";
    return new URLSearchParams(window.location.search).get("sort_dir") ?? "asc";
});

const isSorted = computed(() => {
    return props.sortBy && currentSortBy.value === props.sortBy;
});

const isSortedAsc = computed(() => {
    return isSorted.value && currentSortDir.value === "asc";
});

const isSortedDesc = computed(() => {
    return isSorted.value && currentSortDir.value === "desc";
});

const toggleSort = () => {
    if (!props.sortBy) return;

    const url = new URL(window.location.href);
    let nextDir = "asc";

    if (currentSortBy.value === props.sortBy) {
        if (currentSortDir.value === "asc") {
            nextDir = "desc";
        } else {
            // Reset sort
            url.searchParams.delete("sort_by");
            url.searchParams.delete("sort_dir");
            url.searchParams.set("page", "1");
            router.visit(url.toString(), {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ["items"],
            });
            return;
        }
    }

    url.searchParams.set("sort_by", props.sortBy);
    url.searchParams.set("sort_dir", nextDir);
    url.searchParams.set("page", "1");

    router.visit(url.toString(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["items"],
    });
};

const currentFilterValue = computed(() => {
    if (!props.filterBy || typeof window === "undefined") return "";
    return (
        new URLSearchParams(window.location.search).get(props.filterBy) ?? ""
    );
});

const isFilterActive = computed(() => {
    return currentFilterValue.value !== "";
});

const setFilter = (val: string | number | null) => {
    if (!props.filterBy) return;

    const url = new URL(window.location.href);

    if (val === null || val === "") {
        url.searchParams.delete(props.filterBy);
    } else {
        url.searchParams.set(props.filterBy, String(val));
    }

    url.searchParams.set("page", "1");
    filterMenuOpen.value = false;

    router.visit(url.toString(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ["items"],
    });
};
</script>

<template>
    <th
        class="px-4 py-3.5 text-xs font-semibold tracking-wider text-neutral-300 uppercase select-none first:pl-6 last:pr-6 last:text-right"
    >
        <div
            class="inline-flex items-center gap-2"
            :class="{
                'w-full justify-end': $attrs.class
                    ?.toString()
                    .includes('text-right'),
            }"
        >
            <!-- Header title & sort clickable button -->
            <button
                v-if="sortBy"
                type="button"
                class="group inline-flex cursor-pointer items-center gap-1.5 text-neutral-300 transition-colors hover:text-white focus:outline-none"
                :title="`Ordenar por ${sortBy}`"
                @click="toggleSort"
            >
                <span><slot></slot></span>
                <span class="inline-flex items-center">
                    <ArrowUp
                        v-if="isSortedAsc"
                        :size="13"
                        class="shrink-0 text-white"
                    />
                    <ArrowDown
                        v-else-if="isSortedDesc"
                        :size="13"
                        class="shrink-0 text-white"
                    />
                    <ArrowUpDown
                        v-else
                        :size="13"
                        class="shrink-0 text-neutral-500 opacity-0 transition-opacity group-hover:opacity-100"
                    />
                </span>
            </button>

            <!-- Plain header title when not sortable -->
            <span v-else class="text-neutral-300">
                <slot></slot>
            </span>

            <!-- Filter popover dropdown -->
            <div
                v-if="filterBy && filterValues"
                ref="filterContainerRef"
                class="relative inline-block text-left"
            >
                <button
                    type="button"
                    class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full transition-all"
                    :class="[
                        isFilterActive
                            ? 'pill-item-active text-white'
                            : 'text-neutral-500 hover:bg-white/10 hover:text-neutral-300',
                    ]"
                    :title="
                        isFilterActive
                            ? 'Filtro ativo (clique para alterar)'
                            : 'Filtrar por esta coluna'
                    "
                    @click.stop="filterMenuOpen = !filterMenuOpen"
                >
                    <Filter :size="12" />
                </button>

                <!-- Filter menu dropdown -->
                <div
                    v-if="filterMenuOpen"
                    class="glass-dropdown absolute top-full left-0 z-50 mt-1.5 min-w-44 origin-top-left p-1 text-xs normal-case shadow-2xl"
                    @click.stop
                >
                    <div
                        class="px-2.5 py-1.5 text-[10px] font-semibold tracking-wider text-neutral-400 uppercase"
                    >
                        Filtrar
                    </div>

                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-between rounded-xl px-2.5 py-1.5 text-left text-xs transition-colors hover:bg-white/10"
                        :class="[
                            !isFilterActive
                                ? 'pill-item-active text-white'
                                : 'text-neutral-300',
                        ]"
                        @click="setFilter(null)"
                    >
                        <span>Todos</span>
                        <Check
                            v-if="!isFilterActive"
                            :size="13"
                            class="text-white"
                        />
                    </button>

                    <div class="my-1 border-t border-white/10" />

                    <div class="max-h-52 space-y-0.5 overflow-y-auto">
                        <button
                            v-for="(label, key) in filterValues"
                            :key="key"
                            type="button"
                            class="flex w-full cursor-pointer items-center justify-between rounded-xl px-2.5 py-1.5 text-left text-xs transition-colors hover:bg-white/10"
                            :class="[
                                currentFilterValue === String(key)
                                    ? 'pill-item-active text-white'
                                    : 'text-neutral-300',
                            ]"
                            @click="setFilter(key)"
                        >
                            <span class="truncate">{{ label }}</span>
                            <Check
                                v-if="currentFilterValue === String(key)"
                                :size="13"
                                class="ml-2 shrink-0 text-white"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </th>
</template>
