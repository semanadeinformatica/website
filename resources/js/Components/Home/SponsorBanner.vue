<script setup lang="ts">
import { computed, ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";
import Sponsor from "./Sponsor.vue";
import type SponsorType from "@/Types/Sponsor";
import type { CompanyUser } from "@/Types/User";
import { OhVueIcon } from "oh-vue-icons";

const carousel = ref<typeof Carousel | null>(null);

const next = () => {
    carousel.value?.next();
};

const prev = () => {
    carousel.value?.prev();
};

const props = defineProps<{
    sponsors: SponsorType[];
    color: string; // TODO: find a way to force this to be in the theme defined this year
    title: string;
    idx: number;
}>();

const numCols = computed(() =>
    props.sponsors?.length > 3
        ? Math.ceil(props.sponsors?.length / 2) * 2
        : props.sponsors?.length * 2,
);

const sizes = ["max-h-72", "max-h-56", "max-h-40"];

const size = computed(() => sizes[props.idx % sizes.length]); // TODO: we know for a fact that there are 3 sizes, so we can just use the idx to get the size. This can change in the following editions so be careful with that
</script>

<template>
    <div :style="`color: ${color}`">
        <p class="text-2xl font-bold">{{ title }}</p>
        <div
            class="max-h grid justify-around justify-items-stretch gap-4 border border-solid border-black p-10 shadow-2xl max-lg:hidden"
            :style="`grid-template-columns: repeat(${numCols}, 1fr)`"
        >
            <template v-if="sponsors.length">
                <Sponsor
                    v-for="(sponsor, i) in sponsors"
                    :key="sponsor.id"
                    :company="sponsor.company?.user as CompanyUser"
                    :class="[
                        sponsors.length > 3 &&
                        sponsors.length % 2 &&
                        i == Math.ceil(props.sponsors.length / 2)
                            ? 'col-start-2'
                            : '',
                        size,
                    ]"
                    class="self-center"
                ></Sponsor>
            </template>
            <template v-else>
                <p class="flex w-fit place-self-center text-2xl font-bold">
                    Em breve...
                </p>
            </template>
        </div>
        <div
            class="relative border border-solid border-black p-10 shadow-2xl lg:hidden"
        >
            <template v-if="sponsors.length">
                <Carousel ref="carousel" :wrap-around="true" :autoplay="2000">
                    <Slide v-for="sponsor in sponsors" :key="sponsor.id">
                        <Sponsor
                            :class="size"
                            :company="sponsor.company?.user as CompanyUser"
                        ></Sponsor>
                    </Slide>
                </Carousel>
                <button
                    class="absolute left-2 top-[calc(50%-20px)]"
                    @click="prev"
                >
                    <OhVueIcon name="io-arrow-back" scale="2"></OhVueIcon>
                </button>
                <button
                    class="absolute right-2 top-[calc(50%-20px)]"
                    @click="next"
                >
                    <OhVueIcon name="io-arrow-forward" scale="2"></OhVueIcon>
                </button>
            </template>
            <template v-else>
                <p
                    class="grid place-self-center self-center text-center text-2xl font-bold"
                >
                    Em breve...
                </p>
            </template>
        </div>
    </div>
</template>

<style>
.vfm__overlay {
    background-color: rgba(248, 245, 231, 0.5);
}
</style>
