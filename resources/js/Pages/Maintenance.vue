<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import GlowingOrbs from "@/Components/UI/GlowingOrbs.vue";
import SocialIcon from "@/Components/UI/SocialIcon.vue";
</script>

<template>
    <Head title="Semana de Informática 2026" />

    <div class="relative min-h-screen bg-[#0d0e12] text-white">
        <GlowingOrbs />

        <main
            class="relative z-10 flex min-h-screen flex-col items-center justify-center px-4 py-16"
        >
            <div class="flex flex-col items-center text-center">
                <img
                    id="svg-logo"
                    class="h-28 p-2 select-none sm:h-36"
                    src="/images/sinf2026.svg"
                    alt="SINF 2026 logo"
                />

                <p
                    class="mt-8 text-lg font-medium tracking-wide text-neutral-300 select-none sm:text-xl"
                >
                    Brevemente
                </p>

                <div
                    class="mt-8 flex items-center justify-center gap-4 select-none"
                >
                    <a
                        href="https://www.instagram.com/sinffeup/"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Instagram"
                        class="pill-container pill-item flex h-10 w-10 items-center justify-center text-neutral-400 transition-colors hover:text-white"
                    >
                        <SocialIcon platform="instagram" :size="18" />
                    </a>
                    <a
                        href="https://www.facebook.com/sinffeup/"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Facebook"
                        class="pill-container pill-item flex h-10 w-10 items-center justify-center text-neutral-400 transition-colors hover:text-white"
                    >
                        <SocialIcon platform="facebook" :size="18" />
                    </a>
                    <a
                        href="https://www.linkedin.com/company/sinffeup"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="LinkedIn"
                        class="pill-container pill-item flex h-10 w-10 items-center justify-center text-neutral-400 transition-colors hover:text-white"
                    >
                        <SocialIcon platform="linkedin" :size="18" />
                    </a>
                </div>
            </div>
        </main>
    </div>
</template>
