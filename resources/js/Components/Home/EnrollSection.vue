<script setup lang="ts">
import FancyButton from "@/Components/FancyButton.vue";

interface Props {
    days: number;
    activities: number;
    talks: number;
    stands: number;
}

const { days, activities, talks, stands } = defineProps<Props>();
</script>

<template>
    <section
        class="relative mx-10 mb-10 flex h-full flex-col pt-20 max-xs:items-center"
    >
        <h2
            class="absolute top-5 mr-[5px] self-center border border-solid border-black bg-2023-red-dark px-2 py-2 text-xl font-bold text-white shadow shadow-2023-bg max-sm:relative max-sm:left-0 max-sm:top-6 sm:mr-[7px] sm:px-10 sm:py-6 sm:text-4xl sm:shadow-md"
        >
            Tudo a postos?
        </h2>
        <div
            class="mx-10 flex h-full flex-1 flex-col items-center border border-solid border-black bg-2023-bg p-3 text-center font-bold text-white shadow-2xl shadow-2023-teal sm:p-12"
        >
            <p class="w-full pt-10 text-xl text-2023-teal sm:text-4xl">
                A SINF está a tua espera!
            </p>
            <div
                class="flex flex-row flex-wrap justify-center gap-4 p-3 text-xl font-bold text-2023-teal max-lg:grid-cols-2 max-xs:grid-cols-1 sm:p-12"
            >
                <span class="text-center">{{ days }} dias</span>
                <span class="text-center">{{ stands }} bancas</span>
                <span class="text-center">{{ talks }} palestras</span>
                <span class="text-center">{{ activities }} atividades</span>
            </div>
            <FancyButton> Inscrever-me </FancyButton>
            <div class="flex h-full w-full">
                <picture class="mx-auto my-10 h-auto max-w-full object-none">
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
