<script setup lang="ts">
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import { inject, onMounted, ref } from "vue";
import VueCookies from "vue-cookies";
import route from "ziggy-js";
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
    <VueFinalModal
        ref="modal"
        v-model="options.modelValue"
        class="flex items-center justify-center"
        content-class="max-w-xl mx-4 p-4 bg-2025-blue border border-black border-solid flex relative justify-center items-center flex-col gap-8 rounded-lg"
        @closed="cacheSeenInfo()"
    >
        <div
            class="flex flex-col items-center gap-6 text-center text-xl text-white"
        >
            <img class="w-80 p-6" src="images/sinf logo.png" />
            <p>
                A <span class="font-bold text-2025-blue-dark">SINF</span> está
                mais interactiva do que nunca!
            </p>
            <ul class="flex flex-col items-center">
                <li>
                    Participa em
                    <a
                        :href="route('program', { day: 6 })"
                        target="_blank"
                        class="text-2025-blue-dark underline"
                        >Workshops e Palestras</a
                    >
                </li>
                <li>
                    Visita
                    <a
                        :href="route('program', { day: 1 })"
                        target="_blank"
                        class="text-2025-blue-dark underline"
                        >Bancas de Empresas</a
                    >
                </li>
                <li>
                    Compete nas
                    <a
                        :href="route('program', { day: 3 })"
                        target="_blank"
                        class="text-2025-blue-dark underline"
                        >Competições de CTF e Programação</a
                    >
                </li>
            </ul>
            <p>
                Ganha pontos e troca-os por prémios na
                <a
                    :href="route('shop.show')"
                    target="_blank"
                    class="text-2025-blue-dark underline"
                    >Loja</a
                >
            </p>
            <PrimaryButton
                color="teal"
                shadow="teal"
                text-size="text-2xl"
                padding="sm:px-8"
                @click="enroll"
                >Inscrever-me</PrimaryButton
            >
        </div>
    </VueFinalModal>
</template>
