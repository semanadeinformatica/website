<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import Footer from "../Components/Footer.vue";
import Banner from "@/Components/Banner.vue";
import Ticket from "@/Components/Ticket.vue";

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
                <div class="flex flex-col items-stretch gap-4 p-4 ">
                    <Ticket state="available" />
                    <Ticket state="acquired" />
                    <Ticket state="used" />
                </div>
                <slot />
            </main>
            <Footer />
        </div>
    </div>
</template>
