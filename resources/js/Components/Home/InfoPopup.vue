<script setup lang="ts">
import Modal from "@/Components/UI/Modal.vue";
import { inject, onMounted, ref } from "vue";
import type { VueCookies } from "vue-cookies";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { ArrowRight } from "@lucide/vue";

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
    <Modal v-model="isOpen" max-width="md" @closed="cacheSeenInfo">
        <div class="flex flex-col">
            <img
                class="h-10 w-auto self-start select-none sm:h-12"
                src="/images/sinf-2026-sm.svg"
                alt="SINF 2026 Logo"
            />

            <h2
                class="mt-5 text-xl font-bold tracking-tight text-white sm:text-2xl"
            >
                A SINF está mais interativa do que nunca!
            </h2>
            <p
                class="mt-2 text-xs leading-relaxed text-neutral-400 sm:text-sm"
            >
                Participa nas atividades da conferência, interage com empresas
                parceiras e ganha SINFrões para trocares por prémios.
            </p>

            <ul class="mt-6 space-y-3 text-xs text-neutral-300 sm:text-sm">
                <li class="flex items-center gap-3">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-neutral-400" />
                    <span>Palestras e workshops com especialistas da indústria</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-neutral-400" />
                    <span>Bancas de empresas com oportunidades de estágio e carreira</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-neutral-400" />
                    <span>Competições com desafios práticos e prémios</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-neutral-400" />
                    <span>Loja SINF para trocares SINFrões por merchandising</span>
                </li>
            </ul>

            <div
                class="mt-8 flex w-full flex-col-reverse items-center justify-end gap-3 sm:flex-row"
            >
                <button
                    type="button"
                    class="w-full cursor-pointer px-4 py-2.5 text-center text-xs font-medium text-neutral-400 transition-colors hover:text-white sm:w-auto sm:text-sm"
                    @click="cacheSeenInfo"
                >
                    Explorar
                </button>

                <PrimaryButton
                    padding="px-5 py-2.5"
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
