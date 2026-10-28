<script setup lang="ts">
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import GameCard from "@/Components/Arcade/GameCard.vue";
import Modal from "@/Components/UI/Modal.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import QuickScroll from "@/Components/UI/QuickScroll.vue";
import type ArcadeGame from "@/Types/Arcade";
import type { User } from "@/Types/User";
import { ArrowRight, Gamepad2, X } from "@lucide/vue";

interface Props {
    games: ArcadeGame[];
    points?: number;
    isEnrolled: boolean;
    isParticipant: boolean;
}

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user as User | undefined);

const selectedGame = ref<ArcadeGame | null>(null);
const isGameModalOpen = ref(false);

function handlePlay(game: ArcadeGame) {
    selectedGame.value = game;
    isGameModalOpen.value = true;
}

function closeGameModal() {
    isGameModalOpen.value = false;
    selectedGame.value = null;
}
</script>

<template>
    <AppLayout title="Arcade">
        <QuickScroll mode="top" />

        <div
            class="relative mx-auto w-full max-w-7xl space-y-8 px-4 py-8 sm:space-y-10 sm:px-6 sm:py-12 lg:px-8"
        >
            <!-- Header Section -->
            <div class="space-y-3 text-center">
                <div class="inline-flex items-center justify-center">
                    <PillSelector
                        :items="[
                            {
                                id: 'arcade-tag',
                                label: 'SINF Arcade',
                                disabled: true,
                            },
                        ]"
                        size="sm"
                        :wrap="false"
                    />
                </div>
                <h1
                    class="text-3xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl"
                >
                    Arcade & Desafios
                </h1>
                <p
                    class="mx-auto max-w-2xl text-sm text-neutral-400 sm:text-base"
                >
                    Aposta os teus SINFrões em mini-jogos, desafia outros
                    participantes em tempo real e multiplica os teus pontos para
                    a loja e sorteios!
                </p>
            </div>

            <!-- User Balance Pill / Enrollment CTA -->
            <div
                v-if="points !== null && points !== undefined"
                class="flex justify-center"
            >
                <PillSelector size="md" :wrap="false">
                    <div
                        class="pill-item cursor-default gap-2.5 hover:bg-transparent"
                    >
                        <span class="text-neutral-400">O teu saldo:</span>
                        <span
                            class="flex items-center gap-1.5 font-bold text-white"
                        >
                            <span class="font-mono text-base font-bold">{{
                                points
                            }}</span>
                            <img
                                class="h-4 w-4 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </span>
                    </div>
                </PillSelector>
            </div>

            <div v-else-if="user && !isEnrolled" class="flex justify-center">
                <PrimaryButton
                    padding="px-4 py-1.5"
                    text-size="text-xs"
                    @click="router.put(route('enroll'))"
                >
                    <span>Inscreve-te na SINF 2026 para ganhares SINFrões</span>
                    <ArrowRight :size="13" class="text-amber-400" />
                </PrimaryButton>
            </div>

            <!-- Games Grid Section -->
            <section class="space-y-6">
                <div
                    v-if="games.length > 0"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3"
                >
                    <GameCard
                        v-for="game in games"
                        :key="game.id"
                        :game="game"
                        :points="points"
                        :is-enrolled="isEnrolled"
                        @play="handlePlay(game)"
                    />
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-24 text-center"
                >
                    <PillSelector
                        :items="[
                            {
                                id: 'soon',
                                label: 'Em breve...',
                                disabled: true,
                            },
                        ]"
                        size="sm"
                        :wrap="false"
                        container-class="mb-4"
                    />
                    <h3 class="text-lg font-bold text-white">
                        Os jogos da Arcade estarão disponíveis em breve
                    </h3>
                    <p class="mt-1 text-sm text-neutral-400">
                        Prepara os teus SINFrões para apostar e competir durante
                        o evento!
                    </p>
                </div>
            </section>
        </div>

        <!-- Game Modal Wrapper for Week 2 integration -->
        <Modal :show="isGameModalOpen" max-width="lg" @close="closeGameModal">
            <div
                v-if="selectedGame"
                class="relative rounded-3xl border border-white/10 bg-neutral-950 p-6 shadow-2xl backdrop-blur-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-white/10 pb-4"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/5 text-amber-400"
                        >
                            <Gamepad2 :size="20" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{ selectedGame.name }}
                            </h3>
                            <span class="font-mono text-xs text-neutral-400">
                                {{ selectedGame.min_wager }}–{{
                                    selectedGame.max_wager
                                }}
                                pts
                            </span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-neutral-400 hover:bg-white/10 hover:text-white"
                        @click="closeGameModal"
                    >
                        <X :size="18" />
                    </button>
                </div>

                <div class="space-y-3 py-8 text-center">
                    <p class="text-sm text-neutral-300">
                        O jogo
                        <strong class="text-white">{{
                            selectedGame.name
                        }}</strong>
                        está configurado e pronto para a fase de construção.
                    </p>
                    <p class="text-xs text-neutral-500">
                        (Módulo individual do jogo será carregado aqui na Semana
                        2)
                    </p>
                </div>

                <div class="flex justify-end pt-2">
                    <PrimaryButton
                        padding="px-4 py-2"
                        text-size="text-xs"
                        @click="closeGameModal"
                    >
                        Fechar
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
