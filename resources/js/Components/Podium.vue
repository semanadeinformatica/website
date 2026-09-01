<script setup lang="ts">
import type CompetitionTeam from "@/Types/CompetitionTeam";
import { type CompetitionPrizes } from "@/Types/Competition";
import CompetitionPodium from "@/Components/Competition/Podium.vue";

interface Props {
    mode?: "winners" | "prizes";
    prizes?: CompetitionPrizes[];
    leaderboard?: CompetitionTeam[];
    isOver?: boolean;
}

withDefaults(defineProps<Props>(), {
    mode: "winners",
    prizes: () => [],
    leaderboard: () => [],
    isOver: false,
});
</script>

<template>
    <CompetitionPodium
        :mode="mode"
        :prizes="prizes"
        :leaderboard="leaderboard"
    />
</template>
