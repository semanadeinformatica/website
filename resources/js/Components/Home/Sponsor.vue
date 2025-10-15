<script setup lang="ts">
import type { CompanyUser } from "@/Types/User";
import { OhVueIcon } from "oh-vue-icons";
import { ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";

const options = ref({
  modelValue: false,
});

interface Props {
  company?: CompanyUser;
}

defineProps<Props>();
</script>

<template>
  <!-- Sponsor logo thumbnail -->
  <img
    v-bind="$attrs"
    class="col-span-2 h-full w-full cursor-pointer object-contain p-10 transition-transform duration-200 hover:scale-105"
    :src="company?.profile_photo_url"
    :alt="company?.name"
    @click="options.modelValue = true"
  />

  <!-- Modal -->
  <VueFinalModal
    v-model="options.modelValue"
    class="flex items-center justify-center"
    overlay-class="!bg-[rgba(0,0,0,0.7)] backdrop-blur-sm"
    content-class="
      max-w-xl min-w-[20em] mx-4 p-8 gap-7
      rounded-2xl bg-2025-blue text-white
      border-0 ring-1 ring-white/10 shadow-none bg-clip-padding outline-none
      flex flex-col items-center justify-center
    "
  >
    <!-- Company logo-->
    <img
      :src="company?.profile_photo_url"
      :alt="company?.name"
      class="mx-auto block h-auto max-w-[18em] p-3 object-contain"
    />

    <!-- Text content -->
    <div class="flex w-full max-w-prose flex-col items-center gap-4 text-center">
      <a
        v-if="company?.usertype?.social_media?.website"
        class="text-2xl font-bold underline decoration-white hover:text-gray-200 transition"
        :href="company.usertype.social_media.website"
        target="_blank"
      >
        {{ company.name }}
        <OhVueIcon class="ml-1 mb-[2px]" name="io-open" fill="white" scale="1.1" />
      </a>
      <span v-else class="text-2xl font-bold underline decoration-white">
        {{ company?.name }}
      </span>

      <div
        class="prose prose-invert text-justify leading-relaxed break-words
               max-md:max-h-96 max-md:overflow-y-auto"
        v-html="company?.usertype?.description_html"
      ></div>
    </div>
  </VueFinalModal>
</template>
