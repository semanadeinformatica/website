<script setup lang="ts">
import FancyButton from "@/Components/FancyButton.vue";
</script>

<template>
    <section
        class="relative flex h-full flex-col pt-20 mx-10 mb-10 max-xs:items-center"
    >
        <h2
            class="absolute self-center top-5 mr-[5px] sm:mr-[7px] border border-solid border-black bg-2023-red-dark px-2 py-2 text-xl font-bold text-white shadow sm:shadow-md shadow-2023-bg max-sm:relative max-sm:left-0 max-sm:top-6 sm:px-10 sm:py-6 sm:text-4xl"
        >
            Tudo a postos?
        </h2>
        <div
            class="mx-10 flex h-full flex-1 flex-col items-center border border-solid border-black bg-2023-bg p-3 sm:p-12 text-center font-bold text-white shadow-2xl shadow-2023-teal"
        >
            <p class="w-full pt-10 text-xl text-2023-teal sm:text-4xl">
                A SINF está a tua espera!
            </p>
            <div
                class="flex flex-row flex-wrap justify-center gap-4 p-3 sm:p-12 text-xl font-bold text-2023-teal max-lg:grid-cols-2 max-xs:grid-cols-1"
            >
                <span class="text-center">7 dias</span>
                <span class="text-center">70 bancas</span>
                <span class="text-center">4 talks</span>
                <span class="text-center">1 workshops</span>
            </div>
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
</template>
