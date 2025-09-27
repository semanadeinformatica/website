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
    <div>
        <Head :title="title" />
        <div class="sticky top-0 z-30">
            <Navbar />
            <Banner />
        </div>
        <main class="min-h-screen bg-2025-bg-gradient py-24">
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
