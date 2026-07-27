<script setup lang="ts">
import type Model from "@/Types/Model";
import Cell from "./Cell.vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    item: Model;
    name: string;
}

defineProps<Props>();
</script>

<template>
    <tr class="even:bg-2023-orange/20 border border-black">
        <Cell>{{ item.id.toString() }}</Cell>
        <slot></slot>
        <Cell>
            <Link :href="route(`admin.${name}.edit`, { id: item.id })"
                >Editar</Link
            >
            <Link
                method="delete"
                :href="route(`admin.${name}.destroy`, { id: item.id })"
                class="ml-2"
                >Apagar</Link
            >
        </Cell>
    </tr>
</template>
