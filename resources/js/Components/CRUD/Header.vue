<script setup lang="ts">
import type MyPageProps from "@/Types/PageProps";
import { Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import route, { type RouteParams } from "ziggy-js";

type Props =
    | {
          sortBy: string;
          filterBy?: never;
          filterValues?: never;
      }
    | {
          filterBy: string;
          filterValues: Record<string, string>;
          sortBy?: never;
      }
    | {
          sortBy?: never;
          filterBy?: never;
          filterValues?: never;
      };

interface PageProps extends Record<string, unknown>, MyPageProps {
    sortBy?: string;
    sortDir?: "asc" | "desc";
    filterBy?: Record<string, string[]>;
}

const props = defineProps<Props>();

const page = usePage<PageProps>();

const open = ref(false);

const sort = computed(() => {
    if (!props.sortBy) return { url: "#", icon: "io-swap-vertical" };

    const router = route();
    return {
        url: route(router.current() ?? "", {
            ...router.params,
            sort_by: props.sortBy,
            sort_dir:
                page.props.sortBy === props.sortBy
                    ? page.props.sortDir === "asc"
                        ? "desc"
                        : "asc"
                    : "asc",
        }),
        icon:
            page.props.sortBy === props.sortBy
                ? page.props.sortDir === "asc"
                    ? "io-arrow-down"
                    : "io-arrow-up"
                : "io-swap-vertical",
    };
});

const filter = computed(() => {
    if (!props.filterBy) return [];

    const router = route();
    console.log(router.params);
    return Object.entries(props.filterValues).map(([value, label]) => {
        const oldValue = page.props.filterBy?.[props.filterBy] ?? [];
        const selected = oldValue.includes(value);

        const newValue = selected
            ? oldValue.filter((v) => v !== value)
            : [...oldValue, value];

        return {
            label,
            value,
            selected: page.props.filterBy?.[props.filterBy].includes(value),
            href: route(router.current() ?? "", {
                ...router.params,
                filter_by: {
                    ...(page.props.filterBy ?? {}),
                    [props.filterBy]: newValue,
                },
            } as unknown as RouteParams), // We need this because the types are wrong 🙃
        };
    });
});
</script>

<template>
    <th class="px-4 py-2 text-start last:text-right">
        <slot></slot>
        <Link
            v-if="sortBy"
            :href="sort.url"
            preserve-scroll
            preserve-state
            :only="['items', 'sortBy', 'sortDir']"
        >
            <v-icon :name="sort.icon" class="ml-2" />
        </Link>
        <span v-if="filterBy" class="relative ml-2 w-min">
            <button @click="open = !open">
                <v-icon name="io-filter" />
            </button>

            <div
                v-show="open"
                class="absolute right-0 flex max-h-[50vh] w-max flex-col gap-4 overflow-y-auto border border-black bg-2023-bg p-2 text-black"
            >
                <Link
                    v-for="f in filter"
                    :key="f.value"
                    :href="f.href"
                    preserve-scroll
                    preserve-state
                    :only="['items', 'filterBy']"
                    class="flex flex-row items-center gap-3"
                >
                    <input type="checkbox" :checked="f.selected" />
                    {{ f.label }}
                </Link>
            </div>
        </span>
    </th>
</template>
