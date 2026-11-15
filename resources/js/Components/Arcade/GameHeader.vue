<script setup lang="ts">
import type ArcadeGame from "@/Types/Arcade";
import GameIcon from "@/Components/Arcade/GameIcon.vue";
import GameLimits from "@/Components/Arcade/GameLimits.vue";

interface Props {
    game: ArcadeGame;
    description?: string;
    showLimits?: boolean;
}

withDefaults(defineProps<Props>(), {
    description: undefined,
    showLimits: true,
});
</script>

<template>
    <div class="px-6 pt-6 sm:px-7 sm:pt-7">
        <div class="flex items-start gap-3.5 pr-10">
            <GameIcon :slug="game.slug" :name="game.name" size="md" />

            <div class="min-w-0 flex-1">
                <h2
                    class="text-lg font-bold tracking-tight text-white sm:text-xl"
                >
                    {{ game.name }}
                </h2>

                <p
                    v-if="description"
                    class="mt-0.5 text-xs leading-relaxed text-neutral-400"
                >
                    {{ description }}
                </p>
            </div>
        </div>

        <GameLimits v-if="showLimits" :game="game" class="mt-4" />
    </div>
</template>
