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
    <div class="flex md:hidden">
        <button 
            aria-label="Open navigation menu"
            class="inline-flex items-center justify-center p-2 text-2023-teal" 
            @click="open = !open"
            >
            <svg
                class="h-6 w-6"
                stroke="currentColor"
                fill="none"
                viewBox="0 0 24 24"
            >
                <path
                    :class="{'hidden': open, 'inline-flex': !open}"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                    :class="{'hidden': !open, 'inline-flex': open}"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>
    <div v-show="open" class="md:hidden absolute z-50 m-0 py-6 left-0 top-[5.65rem] w-full bg-2023-teal-dark flex flex-col text-2xl text-2023-bg font-semibold">
        <template v-for="page in Object.keys(props.options['pages'])">
            <ResponsiveNavLink :href="route('dashboard')" :active="page == 'Sponsors'">
                {{ page }}
            </ResponsiveNavLink>
        </template>
        <section class="pt-6">
            <h2 class="pb-3 text-center text-2023-orange font-bold">Atividades</h2>
            <template v-for="activity in Object.keys(props.options['activities'])">
                <ResponsiveNavLink :href="route('dashboard')" :active="activity == 'CTF'">
                    {{ activity }}
                </ResponsiveNavLink>
            </template>
        </section>
        <section class="py-6">
            <h2 class="pb-3 text-center text-2023-orange font-bold">Edições</h2>
            <div class="flex flex-row justify-center gap-5">
                <ResponsiveNavLink :href="route('dashboard')" :active="false">
                    2023
                </ResponsiveNavLink>
            </div>
        </section>
    </div>
</template>