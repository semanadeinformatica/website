<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Navbar from "@/Components/Navigation/Navbar.vue";
import Footer from "@/Components/Navigation/Footer.vue";
import GlowingOrbs from "@/Components/UI/GlowingOrbs.vue";

interface Props {
    title: string;
}

defineProps<Props>();
</script>

<template>
    <div class="relative flex min-h-screen flex-col bg-sinf-surface-dark text-white">
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
