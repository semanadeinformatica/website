<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { router } from "@inertiajs/vue3";
import route from "ziggy-js";
</script>

<template>
    <section
        id="enroll-section"
        class="relative mx-10 flex h-full flex-col items-center max-xs:items-center"
    >
        <p
            class="w-fit py-2 text-center text-3xl font-bold text-text-color sm:text-4xl"
        >
            Estamos à tua espera!
        </p>
        <div
            class="mx-10 flex h-full flex-1 flex-col items-center bg-2025-bg-green p-3 text-center text-3xl font-bold text-white sm:p-12"
        >
            <PrimaryButton
                class="bg-2025-blue "
                shadow="teal"
                text-size="sm:text-4xl"
                padding="sm:px-8"
                @click="
                    $page.props.auth.user
                        ? router.put(route('enroll'))
                        : router.get(route('register'))
                "
                >Inscrever-me</PrimaryButton
            >
        </div>
    </section>
</template>
