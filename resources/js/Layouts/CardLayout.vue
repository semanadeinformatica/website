<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "./AppLayout.vue";

interface Props {
    title: string;
    heading?: string;
    subtitle?: string;
    maxWidth?: string;
}

withDefaults(defineProps<Props>(), {
    heading: undefined,
    subtitle: undefined,
    maxWidth: "max-w-lg",
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
</script>

<template>
    <AppLayout :title="title">
        <div
            class="relative flex min-h-[calc(100vh-8rem)] flex-col items-center justify-center px-4 py-12 sm:py-16"
        >
            <div v-if="showAuthTabs" class="mb-6 flex justify-center">
                <div class="pill-container gap-1 p-1">
                    <Link
                        :href="route('login')"
                        class="pill-item cursor-pointer px-6 py-2 text-xs font-medium transition-all duration-200 sm:text-sm"
                        :class="{
                            'pill-item-active': currentRouteName === 'login',
                        }"
                    >
                        <span>Iniciar sessão</span>
                    </Link>
                    <Link
                        :href="route('register')"
                        class="pill-item cursor-pointer px-6 py-2 text-xs font-medium transition-all duration-200 sm:text-sm"
                        :class="{
                            'pill-item-active': currentRouteName === 'register',
                        }"
                    >
                        <span>Criar conta</span>
                    </Link>
                </div>
            </div>

            <div
                v-else-if="heading || title"
                class="mb-6 flex flex-col items-center text-center"
            >
                <h1
                    class="text-2xl font-bold tracking-tight text-white sm:text-3xl"
                >
                    {{ heading ?? title }}
                </h1>

                <p v-if="subtitle" class="mt-2 text-sm text-neutral-400">
                    {{ subtitle }}
                </p>
            </div>

            <div
                class="relative flex w-full flex-col gap-6 rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 select-none hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8 md:p-10"
                :class="maxWidth"
            >
                <slot />
            </div>
        </div>
    </AppLayout>
</template>
