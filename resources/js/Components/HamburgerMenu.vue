<script setup lang="ts">
    import { ref } from 'vue';
    import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
    import route from 'ziggy-js';
    
    const open = ref(false);

    const props = defineProps({
        options: Object,
    })
</script>

<template>
    <button 
        aria-label="Open navigation menu"
        class="inline-flex items-center justify-center p-2 text-2023-teal hover:text-2023-bg hover:bg-2023-teal focus:outline-none
                focus:bg-2023-teal focus:text-2023-bg transition duration-150 ease-in-out" 
        @click="open = true"
        >
        <svg
            class="h-6 w-6"
            stroke="currentColor"
            fill="none"
            viewBox="0 0 24 24"
        >
            <path
                class="inline-flex"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
            />
        </svg>
    </button>
    <div v-show="open" class="fixed z-50 m-0 top-0 left-0 w-screen h-screen bg-2023-bg">
        <button
            aria-label="Close navigation menu"
            class="absolute mx-2 my-6 px-2 text-4xl text-2023-teal hover:text-2023-bg hover:bg-2023-teal focus:outline-none
                focus:bg-2023-teal focus:text-2023-bg transition duration-150 ease-in-out"
            @click="open = false"
        >
            ←
        </button>
        <div class="flex flex-col w-screen h-screen text-3xl sm:text-5xl justify-center items-center">
            <template v-for="page in Object.keys(props.options)">
                <ResponsiveNavLink :href="route('dashboard')" :active="page == 'Sponsors'">
                    {{ page }}
                </ResponsiveNavLink>
            </template>
        </div>
    </div>
</template>