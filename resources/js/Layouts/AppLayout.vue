<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";
import Banner from "@/Components/Banner.vue";

interface Props {
    title: string;
}

defineProps<Props>();
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <Head :title="title" />
        <div class="sticky top-0 z-30">
            <Navbar />
            <Banner />
        </div>
        <main class="flex-1 bg-2023-bg">
            <slot />
        </main>
        <Footer />
    </div>
</template>

<style>
html {
    scroll-behavior: smooth;
}
</style>
