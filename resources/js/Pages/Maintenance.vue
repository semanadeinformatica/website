<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import GlowingOrbs from "@/Components/UI/GlowingOrbs.vue";
</script>

<template>
    <Head title="semana de informática 2026" />

    <div class="relative min-h-screen bg-olive-950 text-white">
        <GlowingOrbs />

        <main class="relative z-10 min-h-screen">
            <div
                class="flex min-h-screen flex-col content-center items-center justify-evenly"
            >
                <div class="relative flex flex-col items-center">
                    <img
                        id="svg-logo"
                        class="h-32 p-4 select-none"
                        src="/images/sinf2026.svg"
                        alt="SINF 2026 logo"
                    />
                    <p
                        class="absolute inset-x-0 top-full mt-12 text-center text-lg font-medium tracking-wide text-white select-none sm:mt-24"
                    >
                        Brevemente
                    </p>
                    <div
                        class="absolute inset-x-0 top-full mt-22 flex flex-row items-center justify-center space-x-3 select-none sm:mt-34"
                    >
                        <a
                            href="https://www.instagram.com/sinffeup/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-white transition-opacity hover:opacity-80"
                        >
                            <img
                                class="w-4"
                                src="/images/instagram.svg"
                                alt="Instagram"
                            />
                        </a>
                        <a
                            href="https://www.facebook.com/sinffeup/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-white transition-opacity hover:opacity-80"
                        >
                            <img
                                class="w-4"
                                src="/images/facebook.svg"
                                alt="Facebook"
                            />
                        </a>
                        <a
                            href="https://www.linkedin.com/company/sinffeup"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-white transition-opacity hover:opacity-80"
                        >
                            <img
                                class="w-4"
                                src="/images/linkedin.svg"
                                alt="LinkedIn"
                            />
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
