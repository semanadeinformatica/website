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
                ["github", "linkedin", "website"].includes(key) &&
                value != null,
        ),
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
    <div class="transition-all duration-500">
        <div
            class="relative flex w-fit flex-col items-center overflow-x-hidden overflow-y-hidden rounded-full"
        >
            <img
                :src="speaker.profile_photo_url"
                class="h-52 w-52 rounded-full border-2 border-solid border-2023-teal-dark object-cover"
                alt=""
            />
            <div
                v-if="Object.keys(socialMedia).length > 0"
                class="socials absolute -bottom-32 flex w-full flex-row items-center justify-center bg-2023-teal-dark pb-10 pt-1 transition-all"
            >
                <a
                    v-for="(social, key, idx) in socialMedia"
                    :key="idx"
                    :href="social"
                    target="_blank"
                >
                    <OhVueIcon
                        fill="white"
                        :name="socialIcon[key]"
                        scale="1.4"
                    ></OhVueIcon>
                </a>
            </div>
        </div>
        <p class="max-w-52 break-words py-4 text-lg text-2023-teal-dark">
            {{ speaker.usertype?.display_name ?? speakerName(speaker.name) }}
        </p>
    </div>
</template>

<style>
.carousel__slide--active div {
    transform: scale(1.1);
}

.carousel__slide--active .socials {
    bottom: -1.75rem; /* 28px */
}

.carousel__slide--active {
    z-index: 99;
}
</style>
