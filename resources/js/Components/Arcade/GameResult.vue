<script setup lang="ts">
import { computed } from "vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { Skull, Trophy, Wallet } from "@lucide/vue";

type Outcome = "win" | "loss" | "cashout";

interface Props {
    outcome: Outcome | null;
    wager?: number;
    payout?: number;
    multiplier?: number;
    code?: string | null;
    playAgainLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    wager: 0,
    payout: 0,
    multiplier: undefined,
    code: null,
    playAgainLabel: "Jogar novamente",
});

const emit = defineEmits<{
    (e: "play-again"): void;
}>();

const config = computed(() => {
    switch (props.outcome) {
        case "loss":
            return {
                icon: Skull,
                title: "Perdeste",
                subtitle: "Não foi desta vez.",
                wrapper: "border-sinf-primary/25 bg-sinf-primary/[0.07]",
                titleClass: "text-sinf-primary-light",
            };
        case "cashout":
            return {
                icon: Wallet,
                title: "Recolheste!",
                subtitle: "Ganhos garantidos.",
                wrapper: "border-amber-500/25 bg-amber-500/[0.07]",
                titleClass: "text-amber-300",
            };
        case "win":
        default:
            return {
                icon: Trophy,
                title: "Ganhaste!",
                subtitle: "Boa jogada!",
                wrapper: "border-emerald-500/25 bg-emerald-500/[0.07]",
                titleClass: "text-emerald-400",
            };
    }
});

const amount = computed(() =>
    props.outcome === "loss" ? props.wager : props.payout,
);
</script>

<template>
    <div v-if="outcome" class="space-y-3">
        <div class="rounded-2xl border px-4 py-3.5" :class="config.wrapper">
            <div class="flex items-center justify-between gap-4">
                <div class="flex min-w-0 items-center gap-2.5">
                    <component
                        :is="config.icon"
                        :size="18"
                        class="shrink-0"
                        :class="config.titleClass"
                    />
                    <div class="min-w-0">
                        <p class="text-sm font-bold" :class="config.titleClass">
                            {{ config.title }}
                        </p>
                        <p class="text-[11px] text-neutral-400">
                            {{ config.subtitle }}
                        </p>
                    </div>
                </div>

                <div class="shrink-0 text-right">
                    <p class="font-mono text-lg font-black text-white">
                        {{ outcome === "loss" ? "−" : "+" }}{{ amount }}
                    </p>
                    <p
                        class="text-[10px] font-medium tracking-wider text-neutral-500 uppercase"
                    >
                        SINFrões
                    </p>
                </div>
            </div>

            <div
                v-if="
                    (outcome === 'win' && multiplier !== undefined) ||
                    (outcome === 'loss' && code)
                "
                class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-white/8 pt-2 text-[11px] text-neutral-400"
            >
                <span v-if="outcome === 'win' && multiplier !== undefined">
                    Multiplicador
                    <strong class="text-white">{{ multiplier }}x</strong>
                </span>
                <span v-if="outcome === 'loss' && code">
                    Código secreto
                    <strong class="tracking-[0.2em] text-white">
                        {{ code }}
                    </strong>
                </span>
            </div>
        </div>

        <PrimaryButton
            class="w-full justify-center"
            @click="emit('play-again')"
        >
            {{ playAgainLabel }}
        </PrimaryButton>
    </div>
</template>
