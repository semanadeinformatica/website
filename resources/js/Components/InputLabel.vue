<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="font-medium text-2023-teal">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
