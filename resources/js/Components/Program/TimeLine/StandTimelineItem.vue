<script setup lang="ts">
import type Stand from "@/Types/Stand";

interface Props {
    stand: Stand;
}

const { stand } = defineProps<Props>();

const {
    sponsor: { tier, company },
} = stand;

const shadowColorMap = {
    PLATINUM: "shadow-2023-orange",
    GOLD: "shadow-2023-teal-dark",
    SILVER: "shadow-2023-red-dark",
} as Record<typeof tier, string>;
</script>

<template>
    <img
        :src="
            company.user?.profile_photo_url ?? 'https://picsum.photos/400/200'
        "
        :alt="`${company.user?.name ?? 'Company'}'s profile picture'`"
        class="border border-solid border-black shadow-lg"
        :class="shadowColorMap[tier]"
        width="200"
        height="100"
    />
</template>
