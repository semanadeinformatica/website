<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="semana de informática 2026" />

    <div class="relative min-h-screen bg-black text-white">
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
