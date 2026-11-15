<script setup lang="ts">
import { computed, ref } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import type FinalPrize from "@/Types/FinalPrize";
import { Gift, Trophy, Coins, MapPin, Sparkles } from "@lucide/vue";

interface Props {
    prizes: FinalPrize[];
}

const props = defineProps<Props>();

const howToOpen = ref(false);

const orderedPrizes = computed(() =>
    [...props.prizes]
        .filter((prize) => prize.place >= 1 && prize.place <= 3)
        .sort((a, b) => a.place - b.place)
        .slice(0, 3),
);

const placeLabel = (place: number) =>
    place === 1 ? "1º Prémio" : place === 2 ? "2º Prémio" : "3º Prémio";

const placeBadgeClass = (place: number) =>
    place === 1
        ? "border-amber-300/40 bg-amber-400/15 text-amber-300"
        : place === 2
          ? "border-slate-300/40 bg-slate-300/15 text-slate-200"
          : "border-amber-600/40 bg-amber-700/20 text-amber-500";

const placeCardClass = (place: number) =>
    place === 1
        ? "border-amber-400/25 hover:border-amber-400/40"
        : place === 2
          ? "border-slate-300/20 hover:border-slate-300/35"
          : "border-amber-700/20 hover:border-amber-700/35";
</script>

<template>
    <section v-if="orderedPrizes.length > 0" class="space-y-7">
        <div class="flex flex-col items-center gap-2 text-center">
            <div
                class="flex items-center gap-2 font-mono text-[10px] font-semibold tracking-[0.2em] text-amber-400 uppercase"
            >
                <Trophy :size="13" />
                <span>Sorteio de encerramento</span>
            </div>
            <h2
                class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl"
            >
                Prémios Finais
            </h2>
            <p
                class="max-w-2xl text-xs leading-relaxed font-light text-neutral-400 sm:text-sm"
            >
                Os prémios finais vão ser sorteados entre os participantes da
                SINF. Quanto mais SINFrões tiveres, maiores são as tuas chances.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <article
                v-for="prize in orderedPrizes"
                :key="prize.id"
                class="group relative overflow-hidden rounded-3xl border bg-black/50 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 hover:scale-[1.015] hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)]"
                :class="placeCardClass(prize.place)"
            >
                <div
                    class="relative flex aspect-square items-center justify-center overflow-hidden bg-[radial-gradient(circle_at_50%_35%,rgba(255,255,255,0.08),transparent_65%)] p-6"
                >
                    <img
                        v-if="prize.final_prize_image_url"
                        :src="prize.final_prize_image_url"
                        :alt="prize.title"
                        class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-105"
                    />
                    <Gift
                        v-else
                        :size="64"
                        class="text-neutral-700 opacity-50"
                    />
                    <span
                        class="absolute top-4 left-4 rounded-full border px-2.5 py-1 font-mono text-[10px] font-semibold tracking-wider uppercase backdrop-blur-md"
                        :class="placeBadgeClass(prize.place)"
                    >
                        {{ placeLabel(prize.place) }}
                    </span>
                </div>

                <div class="px-5 pt-1 pb-5 text-center">
                    <h3
                        class="text-sm font-bold tracking-tight text-white sm:text-base"
                    >
                        {{ prize.title }}
                    </h3>
                </div>
            </article>
        </div>

        <div class="flex justify-center">
            <PrimaryButton
                color="primary"
                padding="px-5 py-2"
                text-size="text-xs sm:text-sm"
                @click="howToOpen = true"
            >
                <Trophy :size="14" />
                <span>Como ganhar os prémios finais?</span>
            </PrimaryButton>
        </div>

        <Modal
            v-model="howToOpen"
            title="Como ganhar os prémios finais?"
            max-width="lg"
        >
            <div class="space-y-5">
                <p class="text-sm leading-relaxed text-neutral-300">
                    Estes prémios vão ser sorteados na nossa sessão de
                    encerramento. Tens de obrigatoriamente estar na sessão para
                    poderes conseguir ganhar os prémios.
                </p>

                <div
                    class="flex items-start gap-3 rounded-2xl border border-amber-500/20 bg-amber-500/8 p-4"
                >
                    <MapPin :size="16" class="mt-0.5 shrink-0 text-amber-400" />
                    <p class="text-xs leading-relaxed text-amber-100/90">
                        Se o teu nome for chamado e não estiveres lá, um novo
                        sorteio irá acontecer.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="pill-item shrink-0 p-2 text-amber-400">
                            <Coins :size="15" />
                        </div>
                        <p class="text-xs leading-relaxed text-neutral-400">
                            Quantos mais SINFrões tiveres na tua conta no ato de
                            sorteio, mais chances tens de ganhar.
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="pill-item shrink-0 p-2 text-amber-400">
                            <Sparkles :size="15" />
                        </div>
                        <p class="text-xs leading-relaxed text-neutral-400">
                            Portanto vai a palestras/workshops, usa a nossa
                            arcade e participa no evento!
                        </p>
                    </div>
                </div>
            </div>

            <template #footer>
                <PrimaryButton
                    color="pill"
                    padding="px-4 py-1.5"
                    text-size="text-xs"
                    @click="howToOpen = false"
                >
                    Entendi
                </PrimaryButton>
            </template>
        </Modal>
    </section>
</template>
