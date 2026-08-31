<script setup lang="ts">
import { computed, ref } from "vue";
import { Carousel, Slide } from "vue3-carousel";
import Sponsor from "./Sponsor.vue";
import type SponsorType from "@/Types/Sponsor";
import type { CompanyUser } from "@/Types/User";
import { OhVueIcon } from "oh-vue-icons";

const carousel = ref<typeof Carousel | null>(null);
const next = () => carousel.value?.next();
const prev = () => carousel.value?.prev();

const props = defineProps<{
    sponsors: SponsorType[];
    color: string;
    title: string;
    idx: number;
}>();

const sizes = ["max-h-72", "max-h-56", "max-h-40"];
const size = computed(() => sizes[props.idx % sizes.length]);
</script>

<template>
    <!-- Desktop -->
    <section
        class="relative flex w-full flex-col items-center gap-8 overflow-hidden rounded-2xl border-[3px] bg-white/5 p-10 shadow-[0_0_40px_-12px_rgba(255,255,255,0.18)] backdrop-blur-xs max-lg:hidden"
        :style="{ '--tier': color, borderColor: 'var(--tier)' }"
    >
        <h3 class="text-3xl font-bold" :style="{ color: 'var(--tier)' }">
            {{ title }}
        </h3>

        <div
            v-if="sponsors.length"
            class="grid w-full justify-items-center gap-10"
            :style="{
                gridTemplateColumns: `repeat(auto-fit, minmax(5em, 1fr))`,
            }"
        >
            <Sponsor
                v-for="sponsor in sponsors"
                :key="sponsor.id"
                :company="sponsor.company?.user as CompanyUser"
                class="flex w-full max-w-[200px] items-center justify-center filter-[drop-shadow(0_0_0_rgba(0,0,0,0))] transition-transform duration-300 hover:scale-105 hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]"
                :style="{
                    '--halo': `color-mix(in srgb, ${color} 40%, white 0%)`,
                }"
            />
        </div>
        <p v-else class="text-2xl font-semibold text-white/80 italic">
            Em breve...
        </p>
    </section>
    <!-- Mobile -->
    <section
        v-if="sponsors.length"
        class="relative -mx-[calc(50vw-50%)] flex w-screen flex-col items-center gap-6 overflow-hidden border-t-[3px] border-b-[3px] border-white/20 bg-white/5 px-4 py-10 backdrop-blur-xs lg:hidden"
        :style="{ '--tier': color, borderColor: 'var(--tier)' }"
    >
        <h3
            class="mb-4 text-center text-2xl font-bold"
            :style="{ color: 'var(--tier)' }"
        >
            {{ title }}
        </h3>

        <!-- Single sponsor -->
        <div v-if="sponsors.length === 1" class="flex w-full justify-center">
            <Sponsor
                :class="[size]"
                :company="sponsors[0].company?.user as CompanyUser"
                class="transition-transform duration-300 hover:scale-105 hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]"
            />
        </div>

        <!-- Multiple sponsors -->
        <template v-else>
            <div class="relative w-full overflow-hidden">
                <Carousel
                    ref="carousel"
                    :wrap-around="true"
                    :autoplay="2500"
                    snap-align="center"
                    :items-to-show="1"
                    :transition="600"
                    class="w-full overflow-hidden"
                >
                    <Slide v-for="sponsor in sponsors" :key="sponsor.id">
                        <div
                            class="flex h-72 items-center justify-center sm:h-88"
                            :class="[size]"
                        >
                            <Sponsor
                                :company="sponsor.company?.user as CompanyUser"
                                class="filter-[drop-shadow(0_0_0_rgba(0,0,0,0))] transition-transform duration-300 hover:scale-105 hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]"
                            />
                        </div>
                    </Slide>
                </Carousel>

                <!-- Controls -->
                <button
                    class="absolute top-1/2 left-3 -translate-y-1/2 text-white opacity-80 transition hover:opacity-100"
                    @click="prev"
                >
                    <OhVueIcon name="io-arrow-back" scale="2" />
                </button>
                <button
                    class="absolute top-1/2 right-3 -translate-y-1/2 text-white opacity-80 transition hover:opacity-100"
                    @click="next"
                >
                    <OhVueIcon name="io-arrow-forward" scale="2" />
                </button>
            </div>
        </template>
    </section>
</template>

<style scoped>
.max-h-72 {
    max-height: 18rem;
}
.max-h-56 {
    max-height: 14rem;
}
.max-h-40 {
    max-height: 10rem;
}

@media (max-width: 640px) {
    .max-h-72 {
        max-height: 16rem;
    }
    .max-h-56 {
        max-height: 12rem;
    }
    .max-h-40 {
        max-height: 9rem;
    }
}
</style>
