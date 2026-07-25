<script setup lang="ts">
import { onUnmounted, ref, watch } from "vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import {
    route,
    type QueryParams,
    type RouteParamsWithQueryOverload,
} from "ziggy-js";
import type Competition from "@/Types/Competition";
import { OhVueIcon } from "oh-vue-icons";

interface Route {
    label: string;
    _query?: QueryParams;
}
type Routes = Record<string, Route>;

const props = defineProps<{
    modelValue?: boolean;
    options: {
        pages: Routes;
        competitions: Competition[];
        editions: number[];
    };
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: boolean): void;
}>();

const internalOpen = ref<boolean>(props.modelValue ?? false);

watch(
    () => props.modelValue,
    (val) => {
        if (val !== undefined && val !== internalOpen.value)
            internalOpen.value = val;
    },
);

watch(internalOpen, (val) => {
    emit("update:modelValue", val);
    document.body.classList.toggle("overflow-hidden", val);
});

onUnmounted(() => {
    document.body.classList.remove("overflow-hidden");
});
</script>

<template>
    <div class="flex md:hidden">
        <button
            aria-label="Open navigation menu"
            class="inline-flex items-center justify-center px-2 text-white"
            @click="internalOpen = !internalOpen"
        >
            <template v-if="!internalOpen">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-10 text-white"
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
                <OhVueIcon
                    name="io-close"
                    scale="2.1"
                    fill="#FFFFFF"
                ></OhVueIcon>
            </template>
        </button>
    </div>
    <teleport to="body">
        <div
            v-show="internalOpen"
            class="fixed top-0 left-0 z-20 m-0 flex h-screen w-full flex-col bg-black/30 py-6 text-2xl font-semibold text-white backdrop-blur-3xl md:hidden"
            style="padding-top: 4.9rem"
        >
            <div class="flex flex-col items-center">
                <template
                    v-for="({ label, _query }, page) in props.options.pages"
                    :key="page"
                >
                    <ResponsiveNavLink
                        :href="
                            route(route().has(page) ? page : 'home', {
                                _query,
                            } as RouteParamsWithQueryOverload)
                        "
                        :active="page === route().current()"
                    >
                        {{ label }}
                    </ResponsiveNavLink>
                </template>
            </div>
            <section
                v-if="props.options.competitions.length > 0"
                class="flex flex-col items-center pt-6"
            >
                <h2 class="text-2025-bg-green pb-3 text-center font-bold">
                    Competições
                </h2>
                <template
                    v-for="competition in props.options.competitions"
                    :key="competition.id"
                >
                    <ResponsiveNavLink
                        :href="route('competition.show', { competition })"
                    >
                        {{ competition.name }}
                    </ResponsiveNavLink>
                </template>
            </section>
            <section class="py-6">
                <h2 class="text-2025-bg-green pb-3 text-center font-bold">
                    Edições
                </h2>
                <div
                    class="flex w-full flex-wrap justify-center gap-5 px-12"
                    style="grid-template-columns: repeat(auto-fill, 100px)"
                >
                    <template
                        v-for="edition in props.options.editions"
                        :key="`navbar-edition-link-${edition}`"
                    >
                        <ResponsiveNavLink
                            :href="`https://${edition}.sinf.pt`"
                            :active="edition === new Date().getFullYear()"
                        >
                            {{ edition }}
                        </ResponsiveNavLink>
                    </template>
                </div>
            </section>
        </div>
    </teleport>
</template>
