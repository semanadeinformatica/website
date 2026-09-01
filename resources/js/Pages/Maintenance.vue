<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="semana de informática 2026" />
    <!-- TODO: localization -->
    <main class="maintenance-bg min-h-screen bg-black">
        <div
            class="flex min-h-screen flex-col content-center items-center justify-evenly"
        >
            <div class="relative flex flex-col items-center">
                <img
                    id="svg-logo"
                    class="w-96 p-4 select-none"
                    src="images/sinf2026.svg"
                    alt="SINF 2026 logo"
                />
                <p
                    class="absolute top-full inset-x-0 mt-12 sm:mt-24 text-center font-['Inter'] text-lg font-medium tracking-wide text-white select-none"
                    style="font-family: 'Inter', sans-serif;"
                >
                    Brevemente
                </p>
                <div
                    class="absolute top-full inset-x-0 mt-22 sm:mt-34 flex flex-row items-center justify-center space-x-3 select-none"
                >
                    <a
                        href="https://www.instagram.com/sinffeup/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-white transition-opacity hover:opacity-80"
                    >
                        <img
                            class="w-4"
                            src="images/instagram.svg"
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
                            src="images/facebook.svg"
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
                            src="images/linkedin.svg"
                            alt="LinkedIn"
                        />
                    </a>
                </div>
            </div>
        </div>
    </main>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');

.maintenance-bg {
    background-image:
        radial-gradient(
            ellipse 70% 20% at 58% 46%,
            rgb(154 0 54 / 100%) 0%,
            rgb(64 0 23 / 0%) 100%
        ),
        radial-gradient(
            ellipse 70% 20% at 42% 54%,
            rgb(106 0 255 / 85%) 0%,
            rgb(34 1 153 / 0%) 100%
        );
}

@media (min-width: 640px) {
    .maintenance-bg {
        background-image:
            radial-gradient(
                ellipse 30% 24% at 58% 44%,
                rgb(154 0 54 / 100%) 0%,
                rgb(64 0 23 / 0%) 100%
            ),
            radial-gradient(
                ellipse 30% 24% at 42% 58%,
                rgb(106 0 255 / 85%) 0%,
                rgb(34 1 153 / 0%) 100%
            );
    }
}
</style>
