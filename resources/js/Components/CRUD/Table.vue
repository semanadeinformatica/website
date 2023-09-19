<script setup lang="ts" generic="T extends Model">
import type Model from "@/Types/Model";
import { onMounted, ref } from "vue";

defineProps<{
    items: T[];
}>();

const colspan = ref(0);

// Kinda cringe
onMounted(() => {
    colspan.value = document.querySelectorAll("th").length;
});
</script>

<template>
    <table class="max-w-full table-fixed overflow-x-auto">
        <thead>
            <slot name="header"></slot>
        </thead>
        <tbody>
            <template v-if="items.length > 0">
                <template v-for="item in items" :key="item.id">
                    <slot name="row" :item="item"></slot>
                </template>
            </template>
            <tr v-else class="border border-black">
                <td :colspan="colspan" class="p-2 text-center text-xl">
                    Nenhum item encontrado
                </td>
            </tr>
        </tbody>
    </table>
</template>
