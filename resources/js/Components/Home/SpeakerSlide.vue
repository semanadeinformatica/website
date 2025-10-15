<script setup lang="ts">
import type { SpeakerUser } from "@/Types/User";
import { computed } from "vue";
import { OhVueIcon } from "oh-vue-icons";

interface Props {
  speaker: SpeakerUser;
}
const props = defineProps<Props>();

const speaker = computed(() => props.speaker);

const socialMedia = computed(() => {
  return Object.fromEntries(
    Object.entries(speaker.value.usertype?.social_media ?? {}).filter(
      ([key, value]) =>
        ["github", "linkedin", "website"].includes(key) && value != null
    )
  ) as Record<"github" | "linkedin" | "website", string>;
});

const socialIcon: Record<string, string> = {
  github: "io-logo-github",
  linkedin: "io-logo-linkedin",
  website: "io-globe",
};

const speakerName = (name: string) => {
  const nameArray = name.split(" ");
  return `${nameArray[0]} ${nameArray[nameArray.length - 1]}`;
};
</script>

<template>
  <div class="transition-all duration-500 flex flex-col items-center">
    <div
      class="relative flex aspect-square w-40 sm:w-48 md:w-56 lg:w-64
             items-center justify-center overflow-hidden rounded-full"
    >
      <img
        :src="speaker.profile_photo_url"
        class="w-full h-full object-cover"
        alt=""
      />
      <div
        v-if="Object.keys(socialMedia).length > 0"
        class="socials absolute -bottom-32 flex w-full flex-row items-center justify-center pb-10 pt-1 transition-all"
      >
        <a
          v-for="(social, key, idx) in socialMedia"
          :key="idx"
          :href="social"
          target="_blank"
        >
          <OhVueIcon
            :fill="'#FFFFFF'"
            :stroke="'#1A74C2'"
            :name="socialIcon[key]"
            scale="1.4"
          />
        </a>
      </div>
    </div>

    <p class="max-w-52 break-words py-4 text-lg text-white text-center">
      {{ speaker.usertype?.display_name ?? speakerName(speaker.name) }}
    </p>
  </div>
</template>

<style>
.carousel__viewport {
  overflow: hidden !important;
}

.carousel__slide {
  display: flex;
  justify-content: center;
}

.carousel__slide--active .rounded-full {
  transform: scale(1.1);
  transform-origin: center;
}

</style>
