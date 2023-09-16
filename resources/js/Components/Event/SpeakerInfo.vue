<script setup lang="ts">
import type Speaker from "@/Types/Speaker";
import { IoAccessibility } from "oh-vue-icons/icons/io";
import { computed } from "vue";

interface Props {
    speaker: Speaker;
    reverse?: number;
}

const { speaker, reverse } = defineProps<Props>();

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
</script>

<template>
    <div
        class="mx-9 flex w-fit flex-row flex-wrap gap-16 pt-10"
        :class="reverse ? 'flex-row-reverse self-end' : ''"
    >
        <img
            class="border-2 border-black shadow-lg shadow-2023-red"
            src="https://picsum.photos/300/300"
            alt=""
        />
        <div class="flex flex-col justify-center gap-10 text-2023-teal-dark">
            <div
                class="flex w-fit flex-row justify-center gap-6"
                :class="reverse ? 'flex-row-reverse self-end' : ''"
            >
                <div :class="reverse ? 'flex flex-col items-end' : ''">
                    <h2 class="text-3xl font-bold uppercase">
                        {{ speaker.name }}
                    </h2>
                    <span class="w-fit text-lg"
                        >{{ speaker.title }} @{{ speaker.organization }}</span
                    >
                </div>
                <div v-if="socials" class="flex items-center gap-1">
                    <a
                        v-for="(social, name) in socials"
                        :key="social"
                        :href="social"
                    >
                        <v-icon
                            v-if="social"
                            :name="socialIcon[name]"
                            scale="1.4"
                        ></v-icon>
                    </a>
                </div>
            </div>
            <p class="col-span-2 row-start-2 max-w-xl text-justify">
                {{ speaker.description }}
            </p>
        </div>
    </div>
</template>

<style></style>
