<script setup lang="ts">
import type { User } from "@/Types/User";

interface Props {
    speaker: User;
}

defineProps<Props>();
</script>

<template>
    <div class="transition-all duration-500">
        <img
            :src="speaker.profile_photo_url"
            class="w-[200px] rounded-full border-2 border-solid border-black object-cover"
            alt=""
        />
        <p class="max-w-[200px] break-words py-4 text-lg text-2023-teal-dark">
            {{ speaker.name.split(" ")[0] }}
            {{ speaker.name.split(" ")[speaker.name.split(" ").length - 1] }}
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
