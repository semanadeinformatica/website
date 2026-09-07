<script setup lang="ts">
import { computed, type Component } from "vue";
import { route } from "ziggy-js";
import AppLayout from "./AppLayout.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";

interface Props {
    title: string;
    heading?: string;
    subtitle?: string;
    icon?: Component;
    maxWidth?: string;
}

withDefaults(defineProps<Props>(), {
    heading: undefined,
    subtitle: undefined,
    icon: undefined,
    maxWidth: "w-full max-w-md mx-auto lg:max-w-none",
});

const currentRouteName = computed(() => {
    try {
        if (route().current("login")) return "login";
        if (route().current("register")) return "register";
        return null;
    } catch {
        return null;
    }
});

const showAuthTabs = computed(() => {
    return (
        currentRouteName.value === "login" ||
        currentRouteName.value === "register"
    );
});

const authTabs = computed<PillOption[]>(() => [
    {
        id: "login",
        label: "Iniciar sessão",
        href: route("login"),
        active: currentRouteName.value === "login",
    },
    {
        id: "register",
        label: "Criar conta",
        href: route("register"),
        active: currentRouteName.value === "register",
    },
]);
</script>

<template>
    <AppLayout :title="title">
        <div
            class="relative flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8 lg:py-16"
        >
            <div
                class="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16 xl:gap-20"
            >
                <div
                    class="flex flex-col items-center text-center lg:items-start lg:text-left"
                >
                    <div
                        v-if="icon"
                        class="mb-6 flex h-16 w-16 items-center justify-center rounded-3xl border border-white/10 bg-black/40 shadow-[0_4px_30px_rgba(0,0,0,0.5),inset_0_1px_1px_rgba(255,255,255,0.1)] backdrop-blur-xl transition-transform duration-300 hover:scale-105 sm:h-20 sm:w-20"
                    >
                        <component
                            :is="icon"
                            class="h-8 w-8 text-white sm:h-10 sm:w-10"
                        />
                    </div>

                    <h1
                        class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl"
                    >
                        {{ heading ?? title }}
                    </h1>

                    <p
                        v-if="subtitle"
                        class="mt-3 max-w-lg text-sm leading-relaxed text-neutral-400 sm:text-base lg:text-lg"
                    >
                        {{ subtitle }}
                    </p>
                </div>

                <div class="flex w-full flex-col items-center justify-center">
                    <div
                        v-if="showAuthTabs"
                        class="mb-8 flex w-full justify-center"
                    >
                        <PillSelector
                            :items="authTabs"
                            size="md"
                            :wrap="false"
                        />
                    </div>

                    <div :class="maxWidth">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
