<script setup lang="ts">
import AppLayout from "./AppLayout.vue";
import Card from "@/Components/UI/Card.vue";

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
</script>

<template>
    <AppLayout :title="title">
        <div
            class="relative flex flex-1 flex-col items-center justify-center px-4 py-12 sm:px-6 sm:py-16 lg:px-8"
        >
            <div class="mx-auto flex w-full max-w-7xl flex-col items-center">
                <div
                    v-if="(heading || title) && !$slots.header"
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
                    <template v-if="$slots.header" #header>
                        <slot name="header" />
                    </template>

                    <slot />

                    <template v-if="$slots.footer" #footer>
                        <slot name="footer" />
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
