<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import FancyButton from "@/Components/FancyButton.vue";
</script>

<template>
    <AppLayout title="Enroll">
        <section
            class="relative flex h-full flex-col pt-20 max-xs:items-center"
        >
            <h2
                class="absolute self-center mr-7 top-5 mx-auto border border-solid border-black bg-2023-red-dark px-2 py-2 text-2xl font-bold text-white shadow-md shadow-2023-bg max-sm:relative max-sm:left-0 max-sm:top-6 sm:px-10 sm:py-6 sm:text-6xl"
            >
                Tudo a postos?
            </h2>
            <div
                class="mx-10 flex h-full flex-1 flex-col items-center border border-solid border-black bg-2023-bg p-12 text-center font-bold text-white shadow-2xl shadow-2023-teal"
            >
                <p class="w-full py-10 text-xl text-2023-teal sm:text-4xl">
                    A SINF está a tua espera!
                </p>
                <FancyButton> Inscrever-me </FancyButton>
                <div class="flex h-full w-full">
                    <picture
                        class="mx-auto my-10 h-auto max-w-full object-none"
                    >
                        <source
                            srcset="https://picsum.photos/1500/500"
                            media="(min-width: 600px)"
                        />
                        <img
                            src="https://picsum.photos/600/1000"
                            alt="SINF event picture"
                        />
                    </picture>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
