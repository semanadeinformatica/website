<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";

interface Props {
    title: string;
}

defineProps<Props>();
</script>

<template>
    <div class="relative min-h-screen bg-black text-white">
        <Head :title="title" />

        <div
            class="pointer-events-none fixed inset-0 z-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="glow-orb-crimson-1 absolute top-[-20%] right-[-15%] h-[85vh] w-[80vw] rounded-[100%] blur-[70px] will-change-transform sm:blur-[100px]"
            />

            <div
                class="glow-orb-purple-1 absolute bottom-[-20%] left-[-15%] h-[85vh] w-[80vw] rounded-[100%] blur-[70px] will-change-transform sm:blur-[100px]"
            />

            <div
                class="glow-orb-crimson-2 absolute right-[-5%] bottom-[-10%] h-[65vh] w-[60vw] rounded-[100%] blur-[60px] will-change-transform sm:blur-[90px]"
            />

            <div
                class="glow-orb-purple-2 absolute top-[-10%] left-[-5%] h-[65vh] w-[60vw] rounded-[100%] blur-[60px] will-change-transform sm:blur-[90px]"
            />
        </div>

        <main class="relative z-10 min-h-screen">
            <Navbar />
            <!-- <Banner /> -->
            <slot />
            <Footer class="relative z-10" />
        </main>
    </div>
</template>
