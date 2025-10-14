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
                class="relative m-6 flex w-full flex-col items-center gap-8 border-white bg-white/5 backdrop-blur-sm border-[3px] shadow-[0_0_40px_-12px_rgba(255,255,255,0.18)]  p-8 sm:max-w-lg rounded-md"
                :class="{ 'pt-14': heading }"
            >
                <h2
                    v-if="heading"
                    class="absolute top-0 z-10 inline-block -translate-y-2/3 border border-white bg-2025-blue-dark px-6 py-3 text-2xl font-bold text-text-color  rounded-md "
                >
                    {{ heading }}
                </h2>

                <slot />
            </div>
        </div>
    </AppLayout>
</template>
