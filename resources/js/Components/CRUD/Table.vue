<script setup lang="ts" generic="T extends Model">
import type Model from "@/Types/Model";

defineProps<{
    items: T[];
}>();
</script>

<template>
    <table class="max-w-full overflow-x-auto">
        <thead>
            <slot name="header"></slot>
        </thead>
        <tbody>
            <template v-for="item in items" :key="item.id">
                <slot name="row" :item="item"></slot>
            </template>
        </tbody>
    </table>
</template>
