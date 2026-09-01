<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
</script>

<template>
    <div id="enroll-section" class="flex flex-col items-center">
        <button
            type="button"
            class="group inline-flex items-center gap-3 rounded-full bg-white px-8 py-4 text-sm font-semibold text-black shadow-[0_0_35px_rgba(255,255,255,0.25)] transition-all duration-200 hover:scale-105 hover:bg-neutral-100 hover:shadow-[0_0_50px_rgba(255,255,255,0.45)] focus:outline-none active:scale-95 sm:text-base"
            @click="
                $page.props.auth.user
                    ? router.put(route('enroll'))
                    : router.get(route('register'))
            "
        >
            <span>Inscrever-me</span>
            <svg
                class="h-4 w-4 text-neutral-800 transition-transform duration-200 group-hover:translate-x-1"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M14 5l7 7m0 0l-7 7m7-7H3"
                />
            </svg>
        </button>
    </div>
</template>
