<script setup lang="ts">
import { provide, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AdminSidebar from "@/Components/Navigation/AdminSidebar.vue";
import Banner from "@/Components/UI/Banner.vue";
import { route } from "ziggy-js";
import { Menu, ExternalLink } from "@lucide/vue";

defineProps<{
    title: string;
}>();

const sidebarOpen = ref(false);

provide("adminSidebarOpen", sidebarOpen);
</script>

<template>
    <div
        class="selection:bg-white/20 relative flex h-screen w-screen overflow-hidden bg-[#090a0c] font-sans text-neutral-200 antialiased selection:text-white"
    >
        <Head :title="title" />

        <AdminSidebar
            :open="sidebarOpen"
            :mobile-open="sidebarOpen"
            @update:open="sidebarOpen = $event"
            @update:mobile-open="sidebarOpen = $event"
            @close="sidebarOpen = false"
        />

        <div
            class="relative z-10 flex h-screen min-w-0 flex-1 flex-col overflow-hidden"
        >
            <header
                class="z-20 flex min-h-16 shrink-0 items-center border-b border-white/10 bg-[#090a0c]/80 px-4 py-3 backdrop-blur-xl sm:h-16 sm:px-6 sm:py-0 lg:px-8"
            >
                <div
                    class="mx-auto flex w-full max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="pill-container flex h-9 w-9 cursor-pointer items-center justify-center text-neutral-300 transition-colors hover:text-white md:hidden"
                            aria-label="Abrir menu"
                            @click="sidebarOpen = true"
                        >
                            <Menu :size="18" />
                        </button>

                        <slot name="header-title">
                            <h1
                                v-if="title"
                                class="text-base font-bold tracking-tight text-white sm:text-lg"
                            >
                                {{ title }}
                            </h1>
                        </slot>
                    </div>

                    <div
                        v-if="$slots['header-actions']"
                        class="flex w-full items-center gap-2 sm:w-auto sm:gap-3"
                    >
                        <slot name="header-actions" />
                    </div>
                </div>
            </header>

            <Banner />

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div class="mx-auto w-full max-w-7xl">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
