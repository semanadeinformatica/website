<script setup lang="ts">
import type { Tier } from "@/Types/Sponsor";
import type Stand from "@/Types/Stand";

interface Props {
    stand: Stand;
}

const { stand } = defineProps<Props>();

const shadowColorMap = {
    PLATINUM: "shadow-2023-orange",
    GOLD: "shadow-2023-teal-dark",
    SILVER: "shadow-2023-red-dark",
} as Record<Tier, string>;
</script>

<template>
    <img
        :src="
            stand.sponsor?.company?.user?.profile_photo_url ??
            'https://picsum.photos/400/200'
        "
        :alt="`${
            stand.sponsor?.company?.user?.name ?? 'Company'
        }'s profile picture'`"
        class="border border-solid border-black shadow-lg"
        :class="shadowColorMap[stand.sponsor?.tier ?? 'SILVER']"
        width="200"
        height="100"
    />
</template>
