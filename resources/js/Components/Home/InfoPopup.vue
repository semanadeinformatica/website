<script setup lang="ts">
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import { inject, onMounted, ref } from "vue";
import VueCookies from "vue-cookies";
import route from "ziggy-js";

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
</script>

<template>
    <VueFinalModal
        ref="modal"
        v-model="options.modelValue"
        class="flex items-center justify-center"
        content-class="max-w-xl mx-4 p-4 bg-2023-bg border border-black border-solid flex relative justify-center items-center flex-col gap-8"
        @closed="cacheSeenInfo()"
    >
        <div
            class="flex flex-col items-center gap-6 text-center text-xl text-2023-teal-dark"
        >
            <img class="w-80" src="images/sinf logo.png" />
            <p>
                A <span class="font-bold text-2023-red-dark">SINF</span> está
                mais interactiva do que nunca!
            </p>
            <ul class="flex flex-col items-center">
                <li>
                    Participa em
                    <a
                        :href="route('program', { day: 1 })"
                        target="_blank"
                        class="text-2023-red underline"
                        >Workshops e Palestras</a
                    >
                </li>
                <li>
                    Visita
                    <a
                        :href="route('program', { day: 6 })"
                        target="_blank"
                        class="text-2023-red underline"
                        >Bancas de Empresas</a
                    >
                </li>
                <li>
                    Compete nas
                    <a
                        :href="route('program', { day: 1 })"
                        target="_blank"
                        class="text-2023-red underline"
                        >Competições de CTF e Programação</a
                    >
                </li>
            </ul>
            <p>
                Ganha pontos e troca-os por prémios na
                <a
                    :href="route('shop')"
                    target="_blank"
                    class="text-2023-red underline"
                    >Loja</a
                >
            </p>
        </div>
    </VueFinalModal>
</template>
