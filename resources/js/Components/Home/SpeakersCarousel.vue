<script setup lang="ts">
import { ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";
import "vue3-carousel/carousel.css";
import SpeakerSlide from "./SpeakerSlide.vue";
import type { SpeakerUser, User } from "@/Types/User";

interface Props {
    speakers: User[];
}

defineProps<Props>();

interface CarouselInstance {
    next: () => void;
    prev: () => void;
}

const carousel = ref<CarouselInstance | null>(null);

const breakpoints = {
    480: {
        itemsToShow: 1.8,
        snapAlign: "center" as const,
    },
    640: {
        itemsToShow: 2.3,
        snapAlign: "center" as const,
    },
    768: {
        itemsToShow: 3.2,
        snapAlign: "center" as const,
    },
    1024: {
        itemsToShow: 4.2,
        snapAlign: "center" as const,
    },
    1280: {
        itemsToShow: 5.2,
        snapAlign: "center" as const,
    },
    1536: {
        itemsToShow: 6.2,
        snapAlign: "center" as const,
    },
};

const next = () => {
    carousel.value?.next();
};

const prev = () => {
    carousel.value?.prev();
};
</script>

<template>
    <div class="relative w-full overflow-hidden">
        <Carousel
            ref="carousel"
            :items-to-show="1.3"
            :wrap-around="speakers.length > 3"
            :breakpoints="breakpoints"
            :transition="800"
            :autoplay="speakers.length > 3 ? 3500 : 0"
            :pause-autoplay-on-hover="true"
            class="w-full"
        >
            <Slide
                v-for="speaker in speakers as SpeakerUser[]"
                :key="speaker.id"
                class="flex h-auto items-stretch px-2 py-4 sm:px-3"
            >
                <SpeakerSlide :speaker="speaker" />
            </Slide>
        </Carousel>

        <div
            v-if="speakers.length > 1"
            class="mt-6 flex items-center justify-center gap-3"
        >
            <button
                type="button"
                class="pill-container h-10 w-10 justify-center text-neutral-300 transition-all hover:scale-110 hover:text-white focus:outline-none active:scale-95"
                aria-label="Previous speaker"
                @click="prev"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
            </button>
            <button
                type="button"
                class="pill-container h-10 w-10 justify-center text-neutral-300 transition-all hover:scale-110 hover:text-white focus:outline-none active:scale-95"
                aria-label="Next speaker"
                @click="next"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>

<style>
.carousel__viewport {
    overflow: hidden;
    padding: 10px 0;
}

.carousel__track {
    display: flex;
    align-items: stretch;
    transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.carousel__slide {
    display: flex;
    align-items: stretch;
    justify-content: center;
    height: auto;
}
</style>
