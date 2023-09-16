<script setup lang="ts">
import { ref } from "vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import route from "ziggy-js";

const open = ref(false);

const props = defineProps<{
    options: {
        pages: Record<string, string>; // FIXME: fix typing
        activities: Record<string, string>; // FIXME: fix typing
        editions: number[];
    };
}>();
</script>

<template>
    <div class="flex md:hidden">
        <button
            aria-label="Open navigation menu"
            class="inline-flex items-center justify-center px-2 text-2023-teal"
            @click="open = !open"
        >
            <template v-if="!open">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-10 text-2023-teal-dark"
                    viewBox="0 0 512 512"
                >
                    <path
                        fill="none"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-miterlimit="10"
                        stroke-width="48"
                        d="M88 152h336M88 256h336M88 360h336"
                    />
                </svg>
            </template>
            <template v-else>
                <v-icon name="io-close" scale="2.1" fill="#025259"></v-icon>
            </template>
        </button>
    </div>
    <div
        v-show="open"
        class="absolute left-0 top-[5.6rem] z-50 m-0 flex w-full flex-col bg-2023-teal-dark py-6 text-2xl font-semibold text-2023-bg md:hidden"
    >
        <template v-for="(label, page) in props.options.pages" :key="page">
            <ResponsiveNavLink
                :href="route(route().has(page) ? page : 'home')"
                :active="page === route().current()"
            >
                {{ label }}
            </ResponsiveNavLink>
        </template>
        <section class="pt-6">
            <h2 class="pb-3 text-center font-bold text-2023-orange">
                Atividades
            </h2>
            <template
                v-for="(activity, page) in props.options.activities"
                :key="page"
            >
                <ResponsiveNavLink
                    :href="route(route().has(page) ? page : 'home')"
                    :active="page === route().current()"
                >
                    {{ activity }}
                </ResponsiveNavLink>
            </template>
        </section>
        <section class="py-6">
            <h2 class="pb-3 text-center font-bold text-2023-orange">Edições</h2>
            <div class="flex flex-row justify-center gap-5">
                <template
                    v-for="edition in props.options.editions"
                    :key="`navbar-edition-link-${edition}`"
                >
                    <ResponsiveNavLink
                        :href="route('dashboard')"
                        :active="edition === new Date().getFullYear()"
                    >
                        {{ edition }}
                    </ResponsiveNavLink>
                </template>
            </div>
        </section>
    </div>
</template>
