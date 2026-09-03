<script setup lang="ts">
import { computed } from "vue";
import { route } from "ziggy-js";
import AppLayout from "./AppLayout.vue";
import Card from "@/Components/UI/Card.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";

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
            class="relative flex min-h-[calc(100vh-8rem)] flex-col items-center justify-center px-4 py-12 sm:py-16"
        >
            <div v-if="showAuthTabs" class="mb-6 flex justify-center">
                <PillSelector :items="authTabs" size="md" :wrap="false" />
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

            <Card
                as="div"
                :interactive="false"
                padding="p-6 sm:p-8 md:p-10"
                class="w-full"
                :class="maxWidth"
            >
                <slot />
            </Card>
        </div>
    </AppLayout>
</template>
