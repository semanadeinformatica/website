<script setup lang="ts">
import type ArcadeGame from "@/Types/Arcade";
import Card from "@/Components/UI/Card.vue";
import GameIcon from "@/Components/Arcade/GameIcon.vue";
import GameLimits from "@/Components/Arcade/GameLimits.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";

interface Props {
    game: ArcadeGame;
    isEnrolled: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    (e: "play"): void;
}>();
</script>

<template>
    <Card
        as="div"
        :interactive="game.is_active"
        padding="p-5 sm:p-6"
        :class="[
            'relative overflow-hidden transition-all duration-300',
            !game.is_active ? 'opacity-60 grayscale' : '',
        ]"
        @click="isEnrolled && game.is_active && emit('play')"
    >
        <div
            v-if="!game.is_active"
            class="absolute inset-0 z-20 flex items-center justify-center bg-black/60 backdrop-blur-[2px]"
        >
            <span
                class="rounded-full border border-white/10 bg-neutral-900/90 px-4 py-1.5 text-xs font-semibold tracking-wider text-neutral-400 uppercase shadow-md"
            >
                Indisponível
            </span>
        </div>

        <div class="flex h-full flex-col gap-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3.5">
                    <GameIcon :slug="game.slug" :name="game.name" size="md" />
                    <h3
                        class="truncate text-lg font-bold tracking-tight text-white sm:text-xl"
                    >
                        {{ game.name }}
                    </h3>
                </div>

                <div class="relative">
                    <div
                        v-if="!isEnrolled"
                        class="group/tooltip relative w-full"
                    >
                        <PrimaryButton disabled> Jogar </PrimaryButton>
                        <div
                            class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 rounded bg-neutral-900 px-2.5 py-1 text-[11px] whitespace-nowrap text-neutral-300 opacity-0 shadow-lg ring-1 ring-white/10 transition-opacity duration-200 group-hover/tooltip:opacity-100"
                        >
                            Inscreve-te para jogar
                        </div>
                    </div>

                    <PrimaryButton
                        v-else
                        :disabled="!game.is_active"
                        @click.stop="emit('play')"
                    >
                        Jogar
                    </PrimaryButton>
                </div>
            </div>

            <GameLimits :game="game" />
        </div>
    </Card>
</template>
