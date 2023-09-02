<script setup lang="ts">
import { ref } from "vue";
import "vue3-carousel/dist/carousel.css";
import { Carousel, Slide } from "vue3-carousel";
import SpeakerSlide from "./SpeakerSlide.vue";

const carousel = ref(null);
let activeSpeaker = 1;
const carouselKey = ref(0);
const breakpoints = {
    1024: {
        itemsToShow: 4,
    },
};

const next = () => {
    carousel.value.next();
};

const prev = () => {
    carousel.value.prev();
};

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
    <div class="h-max min-h-max pt-20">
        <Carousel
            ref="carousel"
            :key="carouselKey"
            :autoplay="0"
            :items-to-show="2"
            :wrap-around="true"
            :breakpoints="breakpoints"
            @slide-start="handleSlideStart"
        >
            <Slide v-for="slide in 10" :key="slide">
                <SpeakerSlide :slideID="slide" :currentSlide="activeSpeaker" />
            </Slide>
        </Carousel>
    </div>
    <div class="align-center flex justify-center gap-3">
        <button @click="prev">
            <v-icon name="fa-arrow-left" fill="#007172" scale="2"></v-icon>
        </button>
        <button @click="next">
            <v-icon name="fa-arrow-right" fill="#007172" scale="2"></v-icon>
        </button>
    </div>
</template>
