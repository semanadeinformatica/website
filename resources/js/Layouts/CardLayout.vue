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
            class="flex min-h-screen flex-col items-center bg-2023-bg pt-6 sm:justify-center sm:pt-0"
        >
            <div
                class="relative m-6 flex w-full flex-col items-center gap-8 border border-black p-8 sm:max-w-lg"
                :class="{ 'pt-14': heading }"
            >
                <h2
                    v-if="heading"
                    class="absolute top-0 z-10 inline-block -translate-y-2/3 border border-black bg-2023-bg px-6 py-3 text-2xl font-bold text-2023-teal-dark shadow-xl shadow-2023-orange"
                >
                    {{ heading }}
                </h2>

                <slot />
            </div>
        </div>
    </AppLayout>
</template>
