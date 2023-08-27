<script setup lang="ts">
<<<<<<< HEAD
import { Head } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import Footer from "../Components/Footer.vue";
=======
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Navbar from '@/Components/Navbar.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
>>>>>>> eb3c3a2 (First iteration of navbar)

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
