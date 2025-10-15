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

const numCols = computed(() =>
  props.sponsors?.length > 3
    ? Math.ceil(props.sponsors?.length / 2) * 2
    : props.sponsors?.length * 2
);
</script>

<template>
  <!-- Desktop -->
  <section
    class="relative flex flex-col items-center gap-8 rounded-2xl p-10
           bg-white/5 backdrop-blur-sm border-[3px]
           shadow-[0_0_40px_-12px_rgba(255,255,255,0.18)]
           w-full max-lg:hidden overflow-hidden"
    :style="{ '--tier': color, borderColor: 'var(--tier)' }"
  >
    <h3 class="text-3xl font-bold" :style="{ color: 'var(--tier)' }">
      {{ title }}
    </h3>

    <div
      v-if="sponsors.length"
      class="grid justify-around justify-items-center gap-10 w-full"
      :style="{ gridTemplateColumns: `repeat(${numCols}, 1fr)` }"
    >
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
        class="transition-transform duration-300 hover:scale-105
               hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]
               [filter:drop-shadow(0_0_0_rgba(0,0,0,0))]"
        :style="{ '--halo': `color-mix(in srgb, ${color} 40%, white 0%)` }"
      />
    </div>

    <p v-else class="text-2xl font-semibold text-white/80 italic">
      Em breve...
    </p>
  </section>
<!-- Mobile -->
<section
  v-if="sponsors.length"
  class="relative flex flex-col items-center gap-6 py-10 px-4
         w-screen -mx-[calc(50vw-50%)]
         bg-white/5 backdrop-blur-sm border-t-[3px] border-b-[3px]
         border-white/20 lg:hidden overflow-hidden"
  :style="{ '--tier': color, borderColor: 'var(--tier)' }"
>
  <h3 class="text-2xl font-bold text-center mb-4" :style="{ color: 'var(--tier)' }">
    {{ title }}
  </h3>

  <!-- Single sponsor -->
  <div v-if="sponsors.length === 1" class="flex justify-center w-full">
    <Sponsor
      :class="[size]"
      :company="sponsors[0].company?.user as CompanyUser"
      class="transition-transform duration-300 hover:scale-105
             hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]"
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
        <Slide v-for="(sponsor, i) in sponsors" :key="sponsor.id">
          <div class="flex justify-center items-center h-[18rem] sm:h-[22rem]" :class="[size]">
            <Sponsor
              :company="sponsor.company?.user as CompanyUser"
              class="transition-transform duration-300 hover:scale-105
                     hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]
                     [filter:drop-shadow(0_0_0_rgba(0,0,0,0))]"
            />
          </div>
        </Slide>
      </Carousel>

      <!-- Controls -->
      <button
        class="absolute left-3 top-1/2 -translate-y-1/2 text-white opacity-80 hover:opacity-100 transition"
        @click="prev"
      >
        <OhVueIcon name="io-arrow-back" scale="2" />
      </button>
      <button
        class="absolute right-3 top-1/2 -translate-y-1/2 text-white opacity-80 hover:opacity-100 transition"
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
