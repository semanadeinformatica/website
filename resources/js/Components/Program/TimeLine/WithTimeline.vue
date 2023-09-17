<script setup lang="ts">
const times = {
    start: "09h00",
    end: "17h00",
};
</script>

<template>
    <section
        class="mt-10 h-full w-full border-l-4 border-2023-teal-dark p-8 py-10 lg:w-4/6"
    >
        <div class="relative mb-8">
            <span
                class="absolute -left-[calc(2rem+0.85rem)] top-1/2 h-6 w-6 -translate-y-1/2 rounded-2xl bg-2023-orange"
            ></span>
            <div class="flex flex-col md:block">
                <span class="text-2xl font-bold text-2023-orange">Início</span>
                <span
                    class="font-bold md:absolute md:-left-32 md:top-1/2 md:-translate-y-1/2 md:text-xl"
                    >{{ times.start }}</span
                >
            </div>
        </div>
        <slot />
        <div class="relative mt-8">
            <span
                class="absolute -left-[calc(2rem+0.85rem)] top-1/2 h-6 w-6 -translate-y-1/2 rounded-2xl bg-2023-orange"
            ></span>
            <div class="flex flex-col md:block">
                <span class="text-2xl font-bold text-2023-orange">Fim</span>
                <span
                    class="font-bold md:absolute md:-left-32 md:top-1/2 md:-translate-y-1/2 md:text-xl"
                    >{{ times.end }}</span
                >
            </div>
        </div>
    </section>
</template>
