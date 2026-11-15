<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import GameCard from "@/Components/Arcade/GameCard.vue";
import GameHeader from "@/Components/Arcade/GameHeader.vue";
import CoinFlip from "@/Components/Arcade/games/CoinFlip.vue";
import BugSweeper from "@/Components/Arcade/games/BugSweeper.vue";
import HighLow from "@/Components/Arcade/games/HighLow.vue";
import Mastermind from "@/Components/Arcade/games/Mastermind.vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import QuickScroll from "@/Components/UI/QuickScroll.vue";
import type ArcadeGame from "@/Types/Arcade";
import type { User } from "@/Types/User";
import { ArrowRight, Gamepad2 } from "@lucide/vue";

interface Props {
    games: ArcadeGame[];
    points?: number;
    isEnrolled: boolean;
    isParticipant: boolean;
}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user as User | undefined);

// Keep the current balance in sync with partial Inertia reloads and with the
// real-time updates emitted by the games themselves.
const displayPoints = ref<number | undefined>(props.points);

watch(
    () => props.points,
    (newPoints) => {
        displayPoints.value = newPoints;
    },
);

const selectedGame = ref<ArcadeGame | null>(null);
const isGameModalOpen = ref(false);

function handlePlay(game: ArcadeGame) {
    selectedGame.value = game;
    isGameModalOpen.value = true;
}

function closeGameModal() {
    isGameModalOpen.value = false;
    selectedGame.value = null;
    router.reload({ only: ["points", "enrollment_points"] });
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
                <h1
                    class="text-3xl font-black tracking-tight text-white sm:text-4xl lg:text-5xl"
                >
                    Arcade
                </h1>
                <p
                    class="mx-auto max-w-2xl text-sm text-neutral-400 sm:text-base"
                >
                    Aposta os teus SINFrões em mini-jogos, desafia outros
                    participantes em tempo real e multiplica os teus pontos para
                    a loja e sorteios!
                </p>
            </div>

            <!-- Enrollment CTA -->
            <div
                v-if="user && isParticipant && !isEnrolled"
                class="flex justify-center"
            >
                <PrimaryButton
                    padding="px-4 py-1.5"
                    text-size="text-xs"
                    @click="router.put(route('enroll'))"
                >
                    <span>Inscreve-te na SINF 2026 para jogares</span>
                    <ArrowRight :size="13" class="text-amber-400" />
                </PrimaryButton>
            </div>

            <!-- Games Grid Section -->
            <section class="space-y-6">
                <div
                    v-if="games.length > 0"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6"
                >
                    <GameCard
                        v-for="game in games"
                        :key="game.id"
                        :game="game"
                        :is-enrolled="isEnrolled"
                        @play="handlePlay(game)"
                    />
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-24 text-center"
                >
                    <Gamepad2 :size="28" class="mb-3 text-neutral-600" />
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

        <!-- Games -->
        <CoinFlip
            v-if="selectedGame?.slug === 'coin-flip'"
            :game="selectedGame"
            :points="displayPoints ?? 0"
            :show="isGameModalOpen"
            @close="closeGameModal"
        />

        <BugSweeper
            v-else-if="selectedGame?.slug === 'bug-sweeper'"
            :game="selectedGame"
            :points="displayPoints ?? 0"
            :show="isGameModalOpen"
            @update:balance="displayPoints = $event"
            @close="closeGameModal"
        />

        <HighLow
            v-else-if="selectedGame?.slug === 'high-low'"
            :game="selectedGame"
            :points="displayPoints ?? 0"
            :show="isGameModalOpen"
            @close="closeGameModal"
        />

        <Mastermind
            v-else-if="selectedGame?.slug === 'mastermind'"
            :game="selectedGame"
            :points="displayPoints ?? 0"
            :show="isGameModalOpen"
            @close="closeGameModal"
        />

        <!-- Fallback for games without a dedicated module -->
        <Modal
            v-else
            :show="isGameModalOpen"
            max-width="lg"
            @close="closeGameModal"
        >
            <template v-if="selectedGame" #header>
                <GameHeader
                    :game="selectedGame"
                    description="Este jogo ainda não está disponível."
                />
            </template>

            <div class="space-y-2 py-6 text-center">
                <Gamepad2 :size="28" class="mx-auto mb-3 text-neutral-600" />
                <p class="text-sm text-neutral-300">
                    O jogo
                    <strong class="text-white">{{ selectedGame?.name }}</strong>
                    está em preparação.
                </p>
                <p class="text-xs text-neutral-500">
                    Volta mais tarde para jogar.
                </p>
            </div>

            <template #footer>
                <PrimaryButton
                    padding="px-4 py-2"
                    text-size="text-xs"
                    @click="closeGameModal"
                >
                    Fechar
                </PrimaryButton>
            </template>
        </Modal>
    </AppLayout>
</template>
