<script setup lang="ts">
import { ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";

const carousel_plat = ref(null);
const carousel_gold = ref(null);
const carousel_silver = ref(null);

const next = (carousel: null) => {
    carousel?.next();
};

const prev = (carousel: null) => {
    carousel?.prev();
};
defineProps<{
    tier: string;
}>();
</script>

<template>
    <div v-if="tier == 'plat'">
        <p class="text-2xl font-bold text-2023-orange">Platinum</p>
        <div
            class="grid grid-cols-3 justify-around gap-4 border border-solid border-black p-10 shadow-2xl shadow-2023-orange max-lg:hidden"
        >
            <img
                class="ml-auto mr-auto"
                src="https://picsum.photos/300/200"
                alt="Company"
            />
            <img
                class="ml-auto mr-auto"
                src="https://picsum.photos/300/200"
                alt="Company"
            />
            <img
                class="ml-auto mr-auto"
                src="https://picsum.photos/300/200"
                alt="Company"
            />
        </div>
        <div
            class="relative border border-solid border-black p-10 shadow-2xl shadow-2023-orange lg:hidden"
        >
            <Carousel ref="carousel_plat" :wrap-around="true" :autoplay="2000">
                <Slide v-for="slide in 10" :key="slide">
                    <img
                        class="ml-auto mr-auto"
                        src="https://picsum.photos/300/200"
                        alt="Company"
                    />
                </Slide>
            </Carousel>
            <button
                class="absolute left-2 top-[calc(50%-20px)]"
                @click="prev(carousel_plat)"
            >
                <v-icon name="fa-arrow-left" fill="#f29325" scale="2"></v-icon>
            </button>
            <button
                class="absolute right-2 top-[calc(50%-20px)]"
                @click="next(carousel_plat)"
            >
                <v-icon name="fa-arrow-right" fill="#f29325" scale="2"></v-icon>
            </button>
        </div>
    </div>
    <div v-else-if="tier == 'gold'">
        <p class="text-2xl font-bold text-2023-teal-dark">Gold</p>
        <div
            class="grid grid-cols-8 grid-rows-2 gap-4 border border-solid border-black p-10 shadow-2xl shadow-2023-teal-dark max-lg:hidden"
        >
            <img
                class="col-start-1 col-end-3 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-3 col-end-5 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-5 col-end-7 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-7 col-end-9 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-2 col-end-4 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-4 col-end-6 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-6 col-end-8 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
        </div>
        <div
            class="relative border border-solid border-black p-10 shadow-2xl shadow-2023-teal-dark lg:hidden"
        >
            <Carousel ref="carousel_gold" :wrap-around="true" :autoplay="2000">
                <Slide v-for="slide in 4" :key="slide">
                    <div class="mx-5 grid grid-cols-2 gap-4">
                        <img
                            class="ml-auto mr-auto"
                            src="https://picsum.photos/300/200"
                            alt="Company"
                        />
                        <img
                            class="ml-auto mr-auto"
                            src="https://picsum.photos/300/200"
                            alt="Company"
                        />
                    </div>
                </Slide>
            </Carousel>
            <button
                class="absolute left-2 top-[calc(50%-20px)]"
                @click="prev(carousel_gold)"
            >
                <v-icon name="fa-arrow-left" fill="#025259" scale="2"></v-icon>
            </button>
            <button
                class="absolute right-2 top-[calc(50%-20px)]"
                @click="next(carousel_gold)"
            >
                <v-icon name="fa-arrow-right" fill="#025259" scale="2"></v-icon>
            </button>
        </div>
    </div>
    <div v-else-if="tier == 'silver'">
        <p class="text-2xl font-bold text-2023-red-dark">Silver</p>
        <div
            class="align-center grid grid-cols-8 grid-rows-2 justify-center gap-4 border border-solid border-black p-10 shadow-2xl shadow-2023-red-dark max-lg:hidden"
        >
            <img
                class="col-start-1 col-end-3 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-3 col-end-5 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-5 col-end-7 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-7 col-end-9 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-2 col-end-4 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-4 col-end-6 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-6 col-end-8 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
        </div>
        <div
            class="relative border border-solid border-black p-10 shadow-2xl shadow-2023-red-dark lg:hidden"
        >
            <Carousel
                ref="carousel_silver"
                :wrap-around="true"
                :autoplay="2000"
            >
                <Slide v-for="slide in 4" :key="slide">
                    <div class="mx-5 grid grid-cols-4 grid-rows-2 gap-4">
                        <img
                            class="col-start-1 col-end-3 ml-auto mr-auto"
                            src="https://picsum.photos/300/200"
                            alt="Company"
                        />
                        <img
                            class="col-start-3 col-end-5 ml-auto mr-auto"
                            src="https://picsum.photos/300/200"
                            alt="Company"
                        />
                        <img
                            class="col-start-2 col-end-4 row-start-2 ml-auto mr-auto"
                            src="https://picsum.photos/300/200"
                            alt="Company"
                        />
                    </div>
                </Slide>
            </Carousel>
            <button
                class="absolute left-2 top-[calc(50%-20px)]"
                @click="prev(carousel_silver)"
            >
                <v-icon name="fa-arrow-left" fill="#b15d5d" scale="2"></v-icon>
            </button>
            <button
                class="absolute right-2 top-[calc(50%-20px)]"
                @click="next(carousel_silver)"
            >
                <v-icon name="fa-arrow-right" fill="#b15d5d" scale="2"></v-icon>
            </button>
        </div>
    </div>
</template>
