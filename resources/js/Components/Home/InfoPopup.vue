<script setup lang="ts">
import Modal from "@/Components/UI/Modal.vue";
import { inject, onMounted, ref } from "vue";
import VueCookies from "vue-cookies";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";
import PrimaryButton from "../PrimaryButton.vue";

const options = ref({
    modelValue: false,
});

const $cookies = inject<typeof VueCookies.VueCookies>("$cookies");

onMounted(() => {
    options.value.modelValue = $cookies?.get("seenInfo") === null;
});

const cacheSeenInfo = () => {
    $cookies?.set("seenInfo", true);
    options.value.modelValue = false;
};

const enroll = () => {
    $cookies?.set("seenInfo", true);
    router.put(route("enroll"));
};
</script>

<template>
    <Modal
        v-model="options.modelValue"
        max-width="xl"
        @closed="cacheSeenInfo"
    >
        <div
            class="flex flex-col items-center gap-6 text-center text-lg text-white"
        >
            <img class="w-72 p-4" src="/images/sinf logo.png" alt="SINF" />
            <p class="font-medium">
                A <span class="text-sinf-primary-light font-bold">SINF</span> está
                mais interativa do que nunca!
            </p>
            <ul class="flex flex-col items-center gap-2 text-sm text-neutral-300">
                <li>
                    Participa em
                    <a
                        :href="route('program', { day: 6 })"
                        target="_blank"
                        class="text-sinf-secondary-light underline hover:text-white"
                        >Workshops e Palestras</a
                    >
                </li>
                <li>
                    Visita
                    <a
                        :href="route('program', { day: 1 })"
                        target="_blank"
                        class="text-sinf-secondary-light underline hover:text-white"
                        >Bancas de Empresas</a
                    >
                </li>
                <li>
                    Compete nas
                    <a
                        :href="route('program', { day: 3 })"
                        target="_blank"
                        class="text-sinf-secondary-light underline hover:text-white"
                        >Competições de CTF e Programação</a
                    >
                </li>
            </ul>
            <p class="text-sm text-neutral-300">
                Ganha pontos e troca-os por prémios na
                <a
                    :href="route('shop.show')"
                    target="_blank"
                    class="text-sinf-secondary-light underline hover:text-white"
                    >Loja</a
                >
            </p>
            <PrimaryButton
                color="gradient"
                text-size="text-base"
                padding="px-8 py-2.5"
                @click="enroll"
            >
                Inscrever-me
            </PrimaryButton>
        </div>
    </Modal>
</template>
