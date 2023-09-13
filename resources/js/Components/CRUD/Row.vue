<script setup lang="ts">
import type Model from "@/Types/Model";
import Cell from "./Cell.vue";
import { Link } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Model;
    name: string;
}

defineProps<Props>();
</script>

<template>
    <tr class="border border-black even:bg-2023-orange even:bg-opacity-20">
        <Cell>{{ item.id.toString() }}</Cell>
        <slot></slot>
        <Cell>
            <Link :href="route(`admin.${name}.edit`, item)">Editar</Link>
            <Link
                method="delete"
                :href="route(`admin.${name}.destroy`, item)"
                class="ml-2"
                >Apagar</Link
            >
        </Cell>
    </tr>
</template>
