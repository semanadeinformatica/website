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

        <div class="mt-5 md:mt-0">
            <div
                class="bg-2023-bg px-4 py-5 sm:p-6"
            >
                <slot name="content" />
            </div>
        </div>
    </div>
</template>
