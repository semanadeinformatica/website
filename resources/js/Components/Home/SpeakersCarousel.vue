<script setup lang="ts">
import { onMounted, ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";
import "vue3-carousel/dist/carousel.css";
import SpeakerSlide from "./SpeakerSlide.vue";
import type { User } from "@/Types/User";

interface Props {
    speakers: User[];
}

defineProps<Props>();

const carousel = ref<typeof Carousel | null>(null);

const breakpoints = {
    1024: {
        itemsToShow: 4,
    },
};

const next = () => {
    carousel.value?.next();
};

const prev = () => {
    carousel.value?.prev();
};

const wrapperHeight = () => {
    const wrapperHeight: number | undefined = document.querySelector(
        "li.carousel__slide--next > div",
    )?.scrollHeight;
    const track: HTMLElement | null =
        document.querySelector("ol.carousel__track");

    if (track)
        track.style.height = wrapperHeight ? wrapperHeight + 80 + "px" : "100%";
};

onMounted(async () => {
    window.addEventListener("resize", wrapperHeight);

    await new Promise((r) => setTimeout(r, 500)).then(wrapperHeight);
});
</script>

<template>
    <Carousel
        ref="carousel"
        snap-align="center"
        :autoplay="2000"
        :items-to-show="2"
        :wrap-around="true"
        :breakpoints="breakpoints"
    >
        <Slide v-for="speaker in speakers" :key="speaker.id">
            <SpeakerSlide :speaker="speaker" />
        </Slide>
    </Carousel>
    <div class="align-center flex justify-center gap-3 text-2023-teal">
        <button @click="prev">
            <v-icon name="io-arrow-back" scale="2"></v-icon>
        </button>
        <button @click="next">
            <v-icon name="io-arrow-forward" scale="2"></v-icon>
        </button>
    </div>
</template>

<style>
.carousel__slide--active {
    opacity: 1;
    transform: scale(1.1);
}
</style>
