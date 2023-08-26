<script setup lang="ts">
interface Props {
    value?: string;
}

defineProps<Props>();
</script>

<template>
    <label class="font-medium text-2023-teal">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
