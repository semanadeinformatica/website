<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import Footer from "../Components/Footer.vue";
import Banner from "@/Components/Banner.vue";

interface Props {
    title: string;
}

defineProps<Props>();
</script>

<template>
    <div>
        <Head :title="title" />
        <div>
            <Navbar />
            <main class="min-h-screen bg-2023-bg">
                <slot />
            </main>
            <Footer />
        </div>
    </div>
</template>
