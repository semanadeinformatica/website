<script setup lang="ts">
import { onMounted, ref } from "vue";
import "vue3-carousel/dist/carousel.css";
import { Carousel, Slide } from "vue3-carousel";
import SpeakerSlide from "./SpeakerSlide.vue";

const carousel = ref<typeof Carousel | null>(null);
let activeSpeaker = 1;
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
        "li.carousel__slide--active:nth-child(11) > div:nth-child(1)",
    )?.scrollHeight;
    const carousel: HTMLElement | null =
        document.querySelector("ol.carousel__track");

    if (carousel)
        carousel.style.height = wrapperHeight ? wrapperHeight + "px" : "100%";
};

onMounted(async () => {
    window.addEventListener("resize", wrapperHeight);

    await new Promise((r) => setTimeout(r, 500)).then(wrapperHeight);
});

const handleSlideStart = (data: {
    slidingToIndex: number;
    slidesCount: number;
}) => {
    if (data.slidingToIndex < 0) {
        activeSpeaker = data.slidesCount - (Math.abs(data.slidingToIndex) - 1);
    } else if (data.slidingToIndex >= data.slidesCount) {
        activeSpeaker = 1 + (data.slidingToIndex - data.slidesCount);
    } else {
        activeSpeaker = data.slidingToIndex + 1;
    }
};
</script>

<template>
    <Carousel
        ref="carousel"
        snap-align="center"
        :autoplay="2000"
        :items-to-show="2"
        :wrap-around="true"
        :breakpoints="breakpoints"
        @slide-start="handleSlideStart"
    >
        <Slide v-for="slide in 10" :key="slide">
            <SpeakerSlide :slideID="slide" :currentSlide="activeSpeaker" />
        </Slide>
    </Carousel>
    <div class="align-center flex justify-center gap-3">
        <button @click="prev">
            <v-icon name="fa-arrow-left" fill="#007172" scale="2"></v-icon>
        </button>
        <button @click="next">
            <v-icon name="fa-arrow-right" fill="#007172" scale="2"></v-icon>
        </button>
    </div>
</template>
