<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import route from "ziggy-js";

interface Props {
    sortBy?: string;
}

const props = defineProps<Props>();

const sort = computed(() => {
    if (!props.sortBy) return { url: "#", icon: "io-swap-vertical" };

    const searchParams = new URLSearchParams(window.location.search);

    const currentColumn = searchParams.get("sort_by");
    const currentDirection = searchParams.get("sort_dir");

    searchParams.set("sort_by", props.sortBy);
    searchParams.set(
        "sort_dir",
        currentColumn === props.sortBy && currentDirection === "asc"
            ? "desc"
            : "asc",
    );

    const router = route();
    return {
        url: route(router.current() ?? "", {
            ...router.params,
            ...Object.fromEntries(searchParams.entries()),
        }),
        icon:
            currentColumn === props.sortBy
                ? currentDirection === "asc"
                    ? "io-arrow-down"
                    : "io-arrow-up"
                : "io-swap-vertical",
    };
});
</script>

<template>
    <th class="px-4 py-2 text-start last:text-right">
        <slot></slot>
        <Link v-if="sortBy" :href="sort.url" preserve-scroll>
            <v-icon :name="sort.icon" class="ml-2" />
        </Link>
    </th>
</template>
