<script setup lang="ts">
import { ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";
import "vue3-carousel/carousel.css";
import SpeakerSlide from "./SpeakerSlide.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import { ChevronLeft, ChevronRight } from "@lucide/vue";
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

const controls: PillOption[] = [
    { id: "prev", icon: ChevronLeft, ariaLabel: "Previous speaker" },
    { id: "next", icon: ChevronRight, ariaLabel: "Next speaker" },
];

const handleControl = (item: PillOption) => {
    if (item.id === "prev") prev();
    else if (item.id === "next") next();
};

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

        <div v-if="speakers.length > 1" class="mt-6 flex justify-center">
            <PillSelector
                :items="controls"
                size="sm"
                :wrap="false"
                @select="handleControl"
            />
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
