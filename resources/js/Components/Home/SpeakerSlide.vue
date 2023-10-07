<script setup lang="ts">
import type { User } from "@/Types/User";

interface Props {
    speaker: User;
}

defineProps<Props>();

const speakerName = (name: string) => {
    const nameArray = name.split(" ");
    return `${nameArray[0]} ${nameArray[nameArray.length - 1]}`;
};
</script>

<template>
    <div class="transition-all duration-500">
        <img
            :src="speaker.profile_photo_url"
            class="h-52 w-52 rounded-full border-2 border-solid border-black object-cover"
            alt=""
        />
        <p class="max-w-52 break-words py-4 text-lg text-2023-teal-dark">
            {{ speakerName(speaker.name) }}
        </p>
    </div>
</template>

<style>
.carousel__slide--active div {
    transform: scale(1.1);
}
.carousel__slide--active {
    z-index: 99;
}
</style>
