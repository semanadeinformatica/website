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
            class="absolute top-1/2 left-[-2.865rem] h-6 w-6 -translate-y-1/2 rounded-2xl bg-white"
        ></span>
        <div class="flex flex-col md:block">
            <span class="text-text-color text-2xl font-bold">{{ label }}</span>
            <span
                v-if="timestamp"
                class="text-text-color font-bold md:absolute md:top-1/2 md:-left-28 md:-translate-y-1/2 md:text-xl"
                >{{ $d(new Date(timestamp), "hourMinute") }}</span
            >
        </div>
    </div>
</template>
