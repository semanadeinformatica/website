<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/UI/Card.vue";

interface Props {
    terms: string;
}

defineProps<Props>();
</script>

<template>
    <AppLayout title="Termos de Utilização">
        <div
            class="relative mx-auto w-full max-w-5xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8"
        >
            <div class="mb-8 text-center sm:mb-10">
                <h1
                    class="text-2xl font-bold tracking-tight text-white sm:text-3xl lg:text-4xl"
                >
                    Termos de Utilização
                </h1>
            </div>

            <Card
                as="div"
                :interactive="false"
                padding="p-6 sm:p-10"
                class="w-full"
            >
                <div
                    class="prose prose-invert max-w-none text-neutral-300"
                    v-html="terms"
                />
            </Card>
        </div>
    </AppLayout>
</template>
