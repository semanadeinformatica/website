<script setup lang="ts">
import { computed } from "vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { RotateCcw, X } from "@lucide/vue";

interface Props {
    show: boolean;
    outcome: "win" | "loss" | "cashout" | null;
    wager: number;
    payout: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "play-again"): void;
}>();

const config = computed(() => {
    switch (props.outcome) {
        case "win":
            return {
                emoji: "🎉",
                title: "Vitória!",
                message: `Ganhou ${props.payout} pts!`,
                bgGradient:
                    "from-emerald-950/90 via-emerald-900/60 to-black/90",
                borderColor: "border-emerald-500/40",
                textColor: "text-emerald-400",
            };
        case "loss":
            return {
                emoji: "💀",
                title: "Derrota",
                message: `Perdeu ${props.wager} pts`,
                bgGradient: "from-red-950/90 via-red-900/60 to-black/90",
                borderColor: "border-red-500/40",
                textColor: "text-red-400",
            };
        case "cashout":
            return {
                emoji: "💰",
                title: "Cashout!",
                message: `Sacou ${props.payout} pts!`,
                bgGradient: "from-amber-950/90 via-amber-900/60 to-black/90",
                borderColor: "border-amber-500/40",
                textColor: "text-amber-400",
            };
        default:
            return null;
    }
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div
            v-if="show && config"
            class="absolute inset-0 z-30 flex items-center justify-center bg-black/75 p-6 backdrop-blur-md"
        >
            <div
                :class="[
                    'relative flex w-full max-w-sm flex-col items-center rounded-3xl border bg-gradient-to-b p-7 text-center shadow-2xl backdrop-blur-xl',
                    config.bgGradient,
                    config.borderColor,
                ]"
            >
                <!-- Animated Emoji / Icon -->
                <div class="mb-3 animate-bounce text-5xl select-none">
                    {{ config.emoji }}
                </div>

                <!-- Title & Amount -->
                <h3
                    :class="[
                        'text-2xl font-black tracking-tight',
                        config.textColor,
                    ]"
                >
                    {{ config.title }}
                </h3>
                <p class="mt-1 text-lg font-bold text-white">
                    {{ config.message }}
                </p>

                <!-- Action Buttons -->
                <div class="mt-6 flex w-full flex-col gap-2.5 sm:flex-row">
                    <button
                        type="button"
                        class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-white/10 bg-white/5 py-2.5 text-xs font-semibold text-neutral-300 transition-colors hover:bg-white/10 hover:text-white"
                        @click="emit('close')"
                    >
                        <X :size="14" />
                        Fechar
                    </button>

                    <PrimaryButton
                        padding="py-2.5 px-4"
                        text-size="text-xs"
                        class="flex-1 justify-center gap-1.5"
                        @click="emit('play-again')"
                    >
                        <RotateCcw :size="14" />
                        Jogar novamente
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Transition>
</template>
