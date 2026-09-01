<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";

import GlowingOrbs from "@/Components/GlowingOrbs.vue";

interface Props {
    title: string;
}

defineProps<Props>();
</script>

<template>
    <div class="relative min-h-screen bg-olive-950 text-white">
        <Head :title="title" />

        <GlowingOrbs />

        <main class="relative z-10 min-h-screen">
            <Navbar />
            <!-- <Banner /> -->
            <slot />
            <Footer class="relative z-10" />
        </main>
    </div>
</template>
