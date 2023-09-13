<script setup lang="ts">
import { ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";
import Sponsor from "./Sponsor.vue";

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
    sponsors: number;
    color: string;
    title: string;
}>();

const numCols: number =
    props.sponsors > 3
        ? props.sponsors % 2
            ? Math.ceil(props.sponsors / 2) * 2
            : Math.ceil(props.sponsors / 2)
        : props.sponsors;
const numRows: number = props.sponsors > 3 ? 2 : 1;

const calculateSponsorPosition = (sponsor: number) => {
    const span = props.sponsors > 3 ? (props.sponsors % 2 ? 2 : 1) : 1;
    const col =
        props.sponsors % 2 ? sponsor : sponsor % Math.ceil(props.sponsors / 2);
    const row = props.sponsors > 3 ? (sponsor % 2 ? 1 : 2) : 1;
    console.log(`grid-area: ${col} ${col + span} ${row} ${row + 1};`);

    return `grid-area: ${row} / ${col} / ${row + 1} / ${col + span} ;`;
};
</script>

<template>
    <div>
        <p class="text-2xl font-bold" :class="textColor[color]">{{ title }}</p>
        <div
            class="grid justify-around gap-4 border border-solid border-black p-10 shadow-2xl max-lg:hidden"
            :class="shadowColor[color]"
            :style="`grid-template-columns: repeat(${numCols}, 1fr); grid-template-rows: repeat(${numRows}, 1fr);`"
        >
            <Sponsor
                v-for="sponsor in sponsors"
                :key="sponsor"
                :position="calculateSponsorPosition(sponsor)"
                src="https://picsum.photos/300/200"
                :url="'https://google.com'"
                :title="sponsor"
                :description="sponsor"
            ></Sponsor>
        </div>
        <div
            class="relative border border-solid border-black p-10 shadow-2xl lg:hidden"
            :class="shadowColor[color]"
        >
            <Carousel ref="carousel" :wrap-around="true" :autoplay="2000">
                <Slide v-for="sponsor in sponsors" :key="sponsor">
                    <Sponsor
                        src="https://picsum.photos/300/200"
                        :url="'https://google.com'"
                        :title="sponsor"
                        :description="sponsor"
                    ></Sponsor>
                </Slide>
            </Carousel>
            <button class="absolute left-2 top-[calc(50%-20px)]" @click="prev">
                <v-icon name="fa-arrow-left" fill="#f29325" scale="2"></v-icon>
            </button>
            <button class="absolute right-2 top-[calc(50%-20px)]" @click="next">
                <v-icon name="fa-arrow-right" fill="#f29325" scale="2"></v-icon>
            </button>
        </div>
    </div>
</template>

<style>
.vfm__overlay {
    background-color: rgba(248, 245, 231, 0.5);
}
</style>
