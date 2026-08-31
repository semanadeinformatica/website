<script setup lang="ts">
import SectionTitle from "./SectionTitle.vue";
</script>

<template>
    <div class="flex flex-col">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="bg-2025-blue mt-5 px-4 py-5 sm:p-6 md:mt-0">
            <slot name="content" />
        </div>
    </div>
</template>
