<script setup lang="ts">
import AppLayout from "./AppLayout.vue";

interface Props {
    title: string;
    heading?: string;
}

defineProps<Props>();
</script>

<template>
    <AppLayout :title="title">
        <div
            class="flex min-h-screen flex-col items-center bg-2025-blue-bg pt-6 sm:justify-center sm:pt-0"
        >
            <div
                class="relative m-6 flex w-full flex-col items-center gap-8 border border-black p-8 sm:max-w-lg"
                :class="{ 'pt-14': heading }"
            >
                <h2
                    v-if="heading"
                    class="absolute top-0 z-10 inline-block -translate-y-2/3 border border-black bg-2025-blue px-6 py-3 text-2xl font-bold text-text-color shadow-xl shadow-2025-bg"
                >
                    {{ heading }}
                </h2>

                <slot />
            </div>
        </div>
    </AppLayout>
</template>
