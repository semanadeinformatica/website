<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import Footer from "../Components/Footer.vue";

interface Props {
    title: string;
}

defineProps<Props>();
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />
        <div>
            <Navbar />
            <main class="min-h-screen bg-2023-bg">
                <slot />
            </main>
            <Footer></Footer>
        </div>
    </div>
</template>
