<script setup lang="ts">
import AppLayout from "../Layouts/AppLayout.vue";

import { Carousel, Slide } from "vue3-carousel";
import "vue3-carousel/dist/carousel.css";
import { ref } from "vue";

const carousel = ref<typeof Carousel | null>(null);

const next = () => {
    carousel.value?.next();
};

const prev = () => {
    carousel.value?.prev();
};

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const slideTo = (index: number, _event: MouseEvent) => {
    carousel.value?.slideTo(index);
};

const breakpoints = {
    1024: {
        itemsToShow: 5,
    },
};
</script>

<template>
    <AppLayout title="Programa">
        <section class="">
            <div class="">
                <Carousel
                    ref="carousel"
                    :autoplay="0"
                    :items-to-show="5"
                    :breakpoints="breakpoints"
                >
                    <Slide v-for="(slide, idx) in 7" :key="slide">
                        <span
                            class="item inline-flex h-16 w-16 items-center justify-center rounded-sm bg-2023-teal-dark text-center text-xl text-white transition-colors duration-500"
                            @click="(event) => slideTo(idx, event)"
                        >
                            {{ slide }}
                        </span>
                    </Slide>
                </Carousel>
                <button @click="prev">
                    <v-icon
                        name="fa-arrow-left"
                        fill="#007172"
                        scale="2"
                    ></v-icon>
                </button>
                <button @click="next">
                    <v-icon
                        name="fa-arrow-right"
                        fill="#007172"
                        scale="2"
                    ></v-icon>
                </button>
            </div>
        </section>
    </AppLayout>
</template>

<style>
.carousel__slide--active .item {
    background-color: rgb(242, 147, 37);
}
</style>
