<script setup lang="ts">
import type Speaker from "@/Types/Speaker";
import { computed } from "vue";

interface Props {
    speaker: Speaker;
    reverse?: number;
    color: string;
}

const { speaker } = defineProps<Props>();

const socials = computed(() => {
    return {
        email: speaker.social_media?.email,
        github: speaker.social_media?.github,
        instagram: speaker.social_media?.instagram,
        linkedin: speaker.social_media?.linkedin,
        twitter: speaker.social_media?.twitter,
        website: speaker.social_media?.website,
    };
});

const socialIcon: Record<string, string> = {
    email: "io-mail",
    github: "io-logo-github",
    instagram: "io-logo-instagram",
    linkedin: "io-logo-linkedin",
    twitter: "io-logo-twitter",
    website: "io-globe",
};

const textColor: Record<string, string> = {
    orange: "text-2023-orange",
    "teal-dark": "text-2023-teal-dark",
    "red-dark": "text-2023-red-dark",
    red: "text-2023-red",
    teal: "text-2023-teal",
};

const shadowColor: Record<string, string> = {
    orange: "shadow-2023-orange",
    "teal-dark": "shadow-2023-teal-dark",
    "red-dark": "shadow-2023-red-dark",
    red: "shadow-2023-red",
    teal: "shadow-2023-teal",
};

const iconColor: Record<string, string> = {
    orange: "#f29325",
    "teal-dark": "#025259",
    "red-dark": "#b15d5d",
    red: "#d94f04",
    teal: "#007172",
};
</script>

<template>
    <div
        class="flex w-fit flex-row flex-wrap gap-8"
        :class="reverse ? 'flex-row-reverse self-end' : ''"
    >
        <div class="flex flex-col items-center gap-5">
            <img
                class="w-52 border-2 border-black shadow-lg"
                :class="shadowColor[color]"
                :src="speaker.profile_photo_url"
                alt=""
            />
            <div class="flex items-center gap-1">
                <a v-for="social in socials" :key="social" :href="social">
                    <v-icon
                        v-if="social"
                        :name="socialIcon[social]"
                        :fill="iconColor[color]"
                        scale="1.4"
                    ></v-icon>
                </a>
            </div>
        </div>
        <div
            class="flex flex-col justify-center gap-10"
            :class="textColor[color]"
        >
            <div
                class="flex w-fit flex-row justify-center gap-6"
                :class="reverse ? 'flex-row-reverse self-end' : ''"
            >
                <div
                    :class="reverse ? 'flex flex-col items-end text-right' : ''"
                >
                    <h2 class="text-3xl font-bold uppercase">
                        {{ speaker.name }}
                    </h2>
                    <h3 class="text-xl font-bold">{{ speaker.title }}</h3>
                    <h3 class="text-xl font-bold">
                        {{ speaker.organization }}
                    </h3>
                </div>
            </div>
            <p
                class="col-span-2 row-start-2 max-w-4xl"
                :class="reverse ? 'text-right' : ''"
            >
                {{ speaker.description }}
            </p>
        </div>
    </div>
</template>

<style></style>
