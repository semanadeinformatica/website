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
  color: string; // e.g. '#1A74C2'
  title: string;
  idx: number;
}>();

const numCols = computed(() =>
  props.sponsors?.length > 3
    ? Math.ceil(props.sponsors?.length / 2) * 2
    : props.sponsors?.length * 2
);

const sizes = ["max-h-72", "max-h-56", "max-h-40"];
const size = computed(() => sizes[props.idx % sizes.length]);
</script>

<template>
  <section
    class="relative flex flex-col items-center gap-8 rounded-2xl p-10
           bg-white/5 backdrop-blur-sm border-[3px] shadow-[0_0_40px_-12px_rgba(255,255,255,0.18)]
           max-lg:p-8"
    :style="{ '--tier': color, borderColor: 'var(--tier)' }"
  >

    <!-- Title -->

    <h3
    class="text-3xl font-bold"
    :style="{ color: 'var(--tier)' }"
    >
    {{ title }}
    </h3>

    <!-- Desktop grid -->
    <div
      v-if="sponsors.length"
      class="grid justify-around justify-items-center gap-10 w-full max-lg:hidden"
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

    <!-- Mobile carousel -->
    <div v-if="sponsors.length" class="relative w-full lg:hidden">
      <Carousel ref="carousel" :wrap-around="true" :autoplay="2500" class="px-8">
        <Slide v-for="sponsor in sponsors" :key="sponsor.id">
          <Sponsor
            :class="size"
            :company="sponsor.company?.user as CompanyUser"
            class="transition-transform duration-300 hover:scale-105
                   hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]"
          />
        </Slide>
      </Carousel>

      <!-- Controls -->
      <button
        class="absolute left-3 top-[calc(50%-20px)] text-white opacity-80 hover:opacity-100 transition"
        @click="prev"
      >
        <OhVueIcon name="io-arrow-back" scale="2" />
      </button>
      <button
        class="absolute right-3 top-[calc(50%-20px)] text-white opacity-80 hover:opacity-100 transition"
        @click="next"
      >
        <OhVueIcon name="io-arrow-forward" scale="2" />
      </button>
    </div>
  </section>
</template>
