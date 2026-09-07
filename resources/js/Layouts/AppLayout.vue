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
    <div class="relative flex min-h-screen flex-col bg-olive-950 text-white">
        <Head :title="title" />

        <GlowingOrbs />

        <div class="relative z-10 flex min-h-screen flex-1 flex-col">
            <Navbar />
            <!-- <Banner /> -->
            <main class="flex flex-1 flex-col">
                <slot />
            </main>
            <Footer class="relative z-10 mt-auto" />
        </div>
    </div>
</template>
