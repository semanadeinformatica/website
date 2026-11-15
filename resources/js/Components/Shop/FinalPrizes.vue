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

const podiumPrizes = computed(() => {
    const byPlace = new Map(
        orderedPrizes.value.map((prize) => [prize.place, prize]),
    );

    return [byPlace.get(2), byPlace.get(1), byPlace.get(3)].filter(
        (prize): prize is FinalPrize => prize !== undefined,
    );
});

const placeLabel = (place: number) =>
    place === 1 ? "1º Prémio" : place === 2 ? "2º Prémio" : "3º Prémio";

const placeLabelClass = (place: number) =>
    place === 1
        ? "text-amber-300"
        : place === 2
          ? "text-slate-300"
          : "text-amber-500";

const figureClass = (place: number) => {
    if (place === 1) {
        return "order-1 z-20 col-span-2 sm:order-2 sm:col-span-1 sm:col-start-2";
    }

    return place === 2
        ? "order-2 z-10 translate-y-4 opacity-80 hover:translate-y-2 hover:opacity-100 sm:order-1 sm:col-start-1 sm:translate-y-8 sm:hover:translate-y-6"
        : "order-3 z-10 translate-y-4 opacity-80 hover:translate-y-2 hover:opacity-100 sm:col-start-3 sm:translate-y-8 sm:hover:translate-y-6";
};

const imageSizeClass = (place: number) =>
    place === 1 ? "w-40 sm:w-64 lg:w-80" : "w-24 sm:w-40 lg:w-48";
</script>

<template>
    <section v-if="podiumPrizes.length > 0" class="space-y-8">
        <h2
            class="text-center text-2xl font-extrabold tracking-tight text-white sm:text-3xl"
        >
            Prémios Finais
        </h2>

        <div
            class="mx-auto grid max-w-5xl grid-cols-2 items-end gap-x-4 gap-y-10 pb-2 sm:grid-cols-[1fr_1.4fr_1fr] sm:gap-6 sm:pb-8 lg:gap-10"
        >
            <figure
                v-for="prize in podiumPrizes"
                :key="prize.id"
                class="group relative flex flex-col items-center text-center transition-all duration-300"
                :class="figureClass(prize.place)"
            >
                <span
                    class="relative z-20 mb-3 text-xs font-medium sm:text-sm"
                    :class="placeLabelClass(prize.place)"
                >
                    {{ placeLabel(prize.place) }}
                </span>

                <div
                    class="relative flex aspect-square items-center justify-center"
                    :class="imageSizeClass(prize.place)"
                >
                    <img
                        v-if="prize.final_prize_image_url"
                        :src="prize.final_prize_image_url"
                        :alt="prize.title"
                        class="relative z-10 h-full w-full object-contain transition-transform duration-500 group-hover:scale-105"
                        :class="
                            prize.place === 1
                                ? 'drop-shadow-[0_24px_50px_rgba(0,0,0,0.6)]'
                                : 'drop-shadow-[0_12px_30px_rgba(0,0,0,0.5)]'
                        "
                    />
                    <Gift
                        v-else
                        :size="prize.place === 1 ? 72 : 44"
                        class="relative z-10 text-neutral-700 opacity-50"
                    />
                </div>

                <figcaption class="mt-4 w-full">
                    <h3
                        class="mx-auto line-clamp-2 max-w-52 font-bold tracking-tight"
                        :class="
                            prize.place === 1
                                ? 'text-lg text-white sm:text-xl'
                                : 'text-xs text-neutral-200/90 sm:text-sm md:text-base'
                        "
                    >
                        {{ prize.title }}
                    </h3>
                </figcaption>
            </figure>
        </div>

        <div class="flex justify-center">
            <PrimaryButton
                color="pill"
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
            :footer-divider="false"
        >
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="pill-item shrink-0 p-2 text-neutral-400">
                        <Trophy :size="15" />
                    </div>
                    <p class="text-sm leading-relaxed text-neutral-300">
                        Estes prémios vão ser sorteados na nossa sessão de
                        encerramento. Tens de obrigatoriamente estar na sessão
                        para poderes conseguir ganhar os prémios.
                    </p>
                </div>

                <div class="flex items-start gap-3">
                    <div class="pill-item shrink-0 p-2 text-neutral-400">
                        <MapPin :size="15" />
                    </div>
                    <p class="text-sm leading-relaxed text-neutral-300">
                        Se o teu nome for chamado e não estiveres lá, um novo
                        sorteio irá acontecer.
                    </p>
                </div>

                <div class="flex items-start gap-3">
                    <div class="pill-item shrink-0 p-2 text-neutral-400">
                        <Coins :size="15" />
                    </div>
                    <p class="text-sm leading-relaxed text-neutral-300">
                        Quantos mais SINFrões tiveres na tua conta no ato de
                        sorteio, mais chances tens de ganhar.
                    </p>
                </div>

                <div class="flex items-start gap-3">
                    <div class="pill-item shrink-0 p-2 text-neutral-400">
                        <Sparkles :size="15" />
                    </div>
                    <p class="text-sm leading-relaxed text-neutral-300">
                        Portanto vai a palestras/workshops, usa a nossa arcade e
                        participa no evento!
                    </p>
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
