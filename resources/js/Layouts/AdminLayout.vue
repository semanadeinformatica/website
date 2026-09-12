<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AdminNavbar from "@/Components/Navigation/AdminNavbar.vue";
import Footer from "@/Components/Navigation/Footer.vue";
import Banner from "@/Components/UI/Banner.vue";

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
            <AdminNavbar />
            <main class="bg-2023-bg min-h-screen">
                <slot />
            </main>
            <Footer />
        </div>
    </div>
</template>
