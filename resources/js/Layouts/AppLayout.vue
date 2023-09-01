<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
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
        <div class="min-h-screen bg-2023-bg">
            <Navbar />
            <main>
                <slot />
            </main>
            <Footer></Footer>
        </div>
    </div>
</template>
