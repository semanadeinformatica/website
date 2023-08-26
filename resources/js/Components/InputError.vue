<script setup lang="ts">
interface Props {
    message?: string;
}

defineProps<Props>();
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-600">
            {{ message }}
        </p>
    </div>
</template>
