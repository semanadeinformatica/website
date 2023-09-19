<script setup lang="ts">
import { computed, ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";
import Sponsor from "./Sponsor.vue";
import type SponsorType from "@/Types/Sponsor";

const carousel = ref<typeof Carousel | null>(null);

const shadowColor: Record<string, string> = {
    orange: "shadow-2023-orange",
    "teal-dark": "shadow-2023-teal-dark",
    "red-dark": "shadow-2023-red-dark",
};

const textColor: Record<string, string> = {
    orange: "text-2023-orange",
    "teal-dark": "text-2023-teal-dark",
    "red-dark": "text-2023-red-dark",
};

const next = () => {
    carousel.value?.next();
};

const prev = () => {
    carousel.value?.prev();
};

const props = defineProps<{
    sponsors: SponsorType[];
    color: string;
    title: string;
}>();

const numCols = computed(() =>
    props.sponsors.length > 3
        ? Math.ceil(props.sponsors.length / 2) * 2
        : props.sponsors.length * 2,
);
</script>

<template>
    <div :class="textColor[color]">
        <p class="text-2xl font-bold">{{ title }}</p>
        <div
            class="grid justify-around justify-items-stretch gap-4 border border-solid border-black p-10 shadow-2xl max-lg:hidden"
            :class="shadowColor[color]"
            :style="`grid-template-columns: repeat(${numCols}, 1fr)`"
        >
            <Sponsor
                v-for="(sponsor, i) in sponsors"
                :key="sponsor.id"
                :sponsor="sponsor"
                :class="
                    sponsors.length > 3 &&
                    sponsors.length % 2 &&
                    i == Math.ceil(props.sponsors.length / 2)
                        ? 'col-start-2'
                        : ''
                "
            ></Sponsor>
        </div>
        <div
            class="relative border border-solid border-black p-10 shadow-2xl lg:hidden"
            :class="shadowColor[color]"
        >
            <Carousel ref="carousel" :wrap-around="true" :autoplay="2000">
                <Slide v-for="sponsor in sponsors" :key="sponsor.id">
                    <Sponsor :sponsor="sponsor"></Sponsor>
                </Slide>
            </Carousel>
            <button class="absolute left-2 top-[calc(50%-20px)]" @click="prev">
                <v-icon name="io-arrow-back" scale="2"></v-icon>
            </button>
            <button class="absolute right-2 top-[calc(50%-20px)]" @click="next">
                <v-icon name="io-arrow-forward" scale="2"></v-icon>
            </button>
        </div>
    </div>
</template>

<style>
.vfm__overlay {
    background-color: rgba(248, 245, 231, 0.5);
}
</style>
