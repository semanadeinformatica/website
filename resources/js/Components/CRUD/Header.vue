<script setup lang="ts">
interface Props {
    sortBy?: string;
}

defineProps<Props>();
</script>

<template>
    <th class="px-4 py-2 text-start last:text-right">
        <slot></slot>
        <v-icon v-if="sortBy" name="io-swap-vertical" class="ml-2" />
    </th>
</template>
