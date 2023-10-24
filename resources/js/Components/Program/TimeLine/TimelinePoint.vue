<script setup lang="ts">
interface Props {
    label: string;
    timestamp?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="relative my-4">
        <span
            class="absolute -left-[calc(2rem+.865rem)] top-1/2 h-6 w-6 -translate-y-1/2 rounded-2xl bg-2023-orange"
        ></span>
        <div class="flex flex-col md:block">
            <span class="text-2xl font-bold text-2023-orange">{{ label }}</span>
            <span
                v-if="timestamp"
                class="font-bold md:absolute md:-left-32 md:top-1/2 md:-translate-y-1/2 md:text-xl"
                >{{ timestamp }}</span
            >
        </div>
    </div>
</template>
