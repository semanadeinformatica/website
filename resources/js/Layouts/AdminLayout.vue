<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AdminNavbar from "@/Components/AdminNavbar.vue";
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
        <Banner />
        <div class="min-h-screen flex flex-col">
            <AdminNavbar />
            <main class="flex-1 bg-2023-bg">
                <slot />
            </main>
            <Footer />
        </div>
    </div>
</template>
