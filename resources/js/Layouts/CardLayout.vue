<script setup>
import AppLayout from "./AppLayout.vue";

defineProps({
    title: String,
    heading: {
        type: String,
        default: null,
    }
});
</script>

<template>
    <AppLayout :title="title">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-2023-bg">
            <div class="w-full sm:max-w-lg m-6 p-8 border border-black flex flex-col items-center gap-8 relative" :class="{'pt-14': heading}">
                <h2 v-if="heading" class="inline-block shadow-xl shadow-2023-orange border border-black py-3 px-6 text-2023-teal-dark font-bold text-2xl absolute top-0 -translate-y-2/3 z-10 bg-2023-bg">{{ heading }}</h2>

                <slot />
            </div>
        </div>
    </AppLayout>
</template>
