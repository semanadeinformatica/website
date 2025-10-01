<script setup lang="ts">
import { onMounted, ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";
import "vue3-carousel/dist/carousel.css";
import SpeakerSlide from "./SpeakerSlide.vue";
import type { SpeakerUser, User } from "@/Types/User";
import { OhVueIcon } from "oh-vue-icons";

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
        :items-to-show="2"
        :autoplay="2000"
        :wrap-around="true"
        :breakpoints="breakpoints"
    >
        <Slide v-for="speaker in speakers as SpeakerUser[]" :key="speaker.id">
            <SpeakerSlide :speaker="speaker" />
        </Slide>
    </Carousel>
    <div class="align-center flex justify-center gap-3 text-white">
        <button @click="prev">
            <OhVueIcon name="io-arrow-back" scale="2"></OhVueIcon>
        </button>
        <button @click="next">
            <OhVueIcon name="io-arrow-forward" scale="2"></OhVueIcon>
        </button>
    </div>
</template>

<style>
.carousel__slide--active {
    opacity: 1;
    transform: scale(1.1);
}
</style>
