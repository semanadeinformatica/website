<script setup lang="ts">
import Modal from "@/Components/UI/Modal.vue";
import { inject, onMounted, ref } from "vue";
import type { VueCookies } from "vue-cookies";
import { route } from "ziggy-js";
import { router, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import {
    Calendar,
    Briefcase,
    Trophy,
    ShoppingBag,
    ArrowRight,
    Sparkles,
} from "@lucide/vue";

const isOpen = ref(false);

const $cookies = inject<VueCookies>("$cookies");

onMounted(() => {
    isOpen.value = $cookies?.get("seenInfo") === null;
});

const cacheSeenInfo = () => {
    $cookies?.set("seenInfo", true);
    isOpen.value = false;
};

const enroll = () => {
    $cookies?.set("seenInfo", true);
    isOpen.value = false;
    router.put(route("enroll"));
};
</script>

<template>
    <Modal v-model="isOpen" max-width="lg" @closed="cacheSeenInfo">
        <div class="flex flex-col">
            <img
                class="h-12 w-auto select-none sm:h-14 self-start"
                src="/images/sinf-2026-sm.svg"
                alt="SINF 2026 Logo"
            />

            <h2 class="mt-6 text-xl font-black tracking-tight text-white sm:text-2xl">
                A SINF está mais interativa do que nunca!
            </h2>
            <p class="mt-2 max-w-md text-xs leading-relaxed text-neutral-400 sm:text-sm">
                Participa nas atividades da conferência, interage com empresas parceiras e ganha SINFrões para trocares por prémios.
            </p>

            <div class="mt-6 grid w-full grid-cols-1 gap-2.5 sm:grid-cols-2 text-left">
                <Link
                    :href="route('program')"
                    class="group flex items-start gap-3 rounded-2xl border border-white/8 bg-white/3 p-3.5 transition-colors hover:border-white/16 hover:bg-white/6"
                    @click="cacheSeenInfo"
                >
                    <div class="min-w-0 flex-1">
                        <h4 class="text-xs font-bold text-white transition-colors group-hover:text-purple-300">
                            Palestras e Workshops
                        </h4>
                        <p class="mt-0.5 text-[11px] leading-snug text-neutral-400">
                            Aprende com especialistas da indústria.
                        </p>
                    </div>
                </Link>

                <Link
                    :href="route('program')"
                    class="group flex items-start gap-3 rounded-2xl border border-white/8 bg-white/3 p-3.5 transition-colors hover:border-white/16 hover:bg-white/6"
                    @click="cacheSeenInfo"
                >
                    <div class="min-w-0 flex-1">
                        <h4 class="text-xs font-bold text-white transition-colors group-hover:text-amber-300">
                            Bancas de Empresas
                        </h4>
                        <p class="mt-0.5 text-[11px] leading-snug text-neutral-400">
                            Conhece oportunidades de estágio e carreira.
                        </p>
                    </div>
                </Link>

                <div
                    class="flex items-start gap-3 rounded-2xl border border-white/8 bg-white/3 p-3.5"
                >
                    <div class="min-w-0 flex-1">
                        <h4 class="text-xs font-bold text-white">
                            Competições
                        </h4>
                        <p class="mt-0.5 text-[11px] leading-snug text-neutral-400">
                            Desafia-te em programação e CTF.
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('shop.show')"
                    class="group flex items-start gap-3 rounded-2xl border border-white/8 bg-white/3 p-3.5 transition-colors hover:border-white/16 hover:bg-white/6"
                    @click="cacheSeenInfo"
                >
                    <div class="min-w-0 flex-1">
                        <h4 class="text-xs font-bold text-white transition-colors group-hover:text-rose-300">
                            Loja SINF
                        </h4>
                        <p class="mt-0.5 text-[11px] leading-snug text-neutral-400">
                            Troca SINFrões por merchandising e prémios.
                        </p>
                    </div>
                </Link>
            </div>

            <div class="mt-6 flex w-full flex-col sm:flex-row items-center justify-center gap-3">
                <PrimaryButton
                    color="pill"
                    padding="px-5 py-2.5"
                    text-size="text-xs sm:text-sm"
                    class="w-full sm:w-auto"
                    @click="cacheSeenInfo"
                >
                    Explorar
                </PrimaryButton>

                <PrimaryButton
                    color="pill"
                    padding="px-6 py-2.5"
                    text-size="text-xs sm:text-sm"
                    class="w-full sm:w-auto"
                    @click="enroll"
                >
                    <span>Inscrever-me na SINF</span>
                    <ArrowRight :size="14" />
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
