<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import 'vue3-carousel/dist/carousel.css';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';
import SpeakerSlide from '../Components/SpeakerSlide.vue';
import AppLayout from '../Layouts/AppLayout.vue';
import {LMap, LTileLayer, LMarker, LControl, LIcon} from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";

const carousel = ref(null);
let activeSpeaker = 1;


const emits = defineEmits(['update:modelValue'])

const zoom = 17;
const center = [41.17835293313974, -8.595830311142494];
const url = "https://tile.openstreetmap.de/{z}/{x}/{y}.png";
const attribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

const addMarker = (e) => {
    if (e.latlng !== undefined) {
        emits("update:modelValue", e.latlng)
    }
}
function next() {
    carousel.value.next();
}

function prev() {
    carousel.value.prev();
}

function handleSlideStart(data) {
    if (data.slidingToIndex < 0) {
        activeSpeaker = data.slidesCount - (Math.abs(data.slidingToIndex) - 1);
    } else if (data.slidingToIndex >= data.slidesCount) {
        activeSpeaker = 1 + (data.slidingToIndex - data.slidesCount);
    } else {
        activeSpeaker = data.slidingToIndex + 1;
    }
}

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    'modelValue': Object,

});
</script>

<template>
    <Head title="Home"/>
    <AppLayout>
        <main class=" bg-2023-bg">
            <!-- LOGO & DATE -->
            <section class="flex flex-col gap-[60px] py-[65px] justify-evenly items-center content-center min-h-screen">
                <img id="svg-1" class="animate-2023-maintenance-jump opacity-50 absolute max-[900px]:hidden w-[150px] left-[7%]" src="images/cy-sinf.svg" alt="Stylized SINF logo">
                <img id="svg-2" class="animate-2023-maintenance-jump opacity-50 absolute max-[900px]:hidden top-[70px] w-[120px] right-[7%]" src="images/rc-sinf.svg" alt="Stylized SINF logo">
                <img id="svg-3" class="animate-2023-maintenance-jump opacity-50 absolute max-[900px]:hidden bottom-[100px] w-[90px] right-[15%]" src="images/oc-sinf.svg" alt="Stylized SINF logo">

                <div class="relative">
                    <img class="w-[450px] max-[900px]:w-[300px]" src="images/sinf logo.png" alt="Stylized SINF logo">
                    <span class="text-2023-teal text-xl font-bold absolute bottom-[-20px] right-0 margin-0">2023</span>
                </div>
                <p class="shadow-md shadow-2023-teal text-2023-teal p-2.5 border border-solid border-black px-8 text-lg font-bold mr-2">semana_de_informática</p>
                <p class="text-2023-teal margin-0 text-2xl font-bold">25 a 31 de outubro</p>
            </section>
            <!-- ABOUT US -->
            <section class="relative grid grid-flow-row grid-cols-2 text-justify bg-2023-teal-dark border-t border-black">
                <h2 class="absolute left-[calc(50%-89px)] bg-2023-red p-3 text-3xl font-bold text-white border border-solid border-black -top-[36px] shadow-md shadow-2023-bg">Sobre nós</h2>
                <p class="p-20 text-white text-lg">
                    A Semana de Informática (SINF), organizada pelo Núcleo de Informática da Associação de Estudantes da Faculdade de Engenharia da Universidade do Porto (NIAEFEUP), foi criada com o intuito de permitir aos estudantes, independentemente do curso, desenvolver as suas capacidades nas diversas áreas da Informática, promovendo a sua interação com o mundo empresarial através de eventos sociais.
                </p>
                <p class="p-20 text-white text-lg">
                    A edição de 2022 decorre entre os dias 31 de outubro a 2 de novembro. Os participantes têm a seu dispor palestras e workshops onde entrarão em contacto com diversas tecnologias e conceitos que não intervêm no percurso académico, focando-se portanto nas skills técnicas, assim como um pitch, sessões de entrevistas e visitas a empresas, fomentando o seu contacto com empresas de topo a nível nacional e internacional, podendo desenvolver as suas soft skills.
                </p>
            </section>
            <!-- CALL TO ACTION -->
            <section class="flex relative py-20">
                <h2 class="absolute border border-solid bg-2023-orange p-2 text-2xl font-bold border-black text-white shadow-md shadow-2023-teal-dark top-[54px] left-[calc(10%+70px)]">Inscreve-te!</h2>
                <div class=" text-2023-teal p-12 border border-solid border-black text-xl flex w-full justify-around mx-[10%] shadow-2xl shadow-2023-orange font-bold">
                    <span>07 dias</span>
                    <span>70 bancas</span>
                    <span>7 talks</span>
                    <span>7 workshops</span>
                </div>
            </section>
            <!-- SPEAKERS -->
            <section class="relative min-h-max h-max pb-20">
                <p class="absolute left-[calc(50%-64px)] top-0 shadow shadow-2023-bg bg-2023-red-dark font-bold w-min text-white p-3 border border-black border-solid text-2xl self-center">Speakers</p>
                <div class=" pt-20 h-max min-h-max">
                    <Carousel @slide-start="handleSlideStart" :items-to-show="4" :wrap-around="true" ref="carousel">
                        <Slide v-for="slide in 10" :key="slide">
                            <SpeakerSlide :slide_id="{slide}" :currentSlide="{activeSpeaker}" />
                        </Slide>
                    </Carousel>
                </div>
                <div class="flex align-center justify-center gap-3">
                    <button @click="prev">
                        <v-icon name="fa-arrow-left" fill="#007172" scale="2"></v-icon>
                    </button>
                    <button @click="next">
                        <v-icon name="fa-arrow-right" fill="#007172" scale="2"></v-icon>
                    </button>
                </div>
            </section>
            <!-- SPONSORS -->
            <section class="relative px-20 py-24 flex flex-col gap-10">
                <p class="absolute left-[calc(50%-64px)] top-0 shadow shadow-2023-bg bg-2023-teal-dark font-bold w-min text-white p-3 border border-black border-solid text-2xl self-center">Sponsors</p>
                <div>
                    <p class=" text-2xl text-2023-orange font-bold">Gold</p>
                    <div class=" shadow-2xl shadow-2023-orange border border-black border-solid flex row justify-around p-10">
                        <img src="https://picsum.photos/300/200" alt="Company">
                        <img src="https://picsum.photos/300/200" alt="Company">
                        <img src="https://picsum.photos/300/200" alt="Company">
                    </div>
                </div>
                <div>
                    <p class=" text-2xl text-2023-teal-dark font-bold">Silver</p>
                    <div class="grid grid-cols-8 grid-rows-2 gap-4 shadow-2xl shadow-2023-teal-dark border border-black border-solid p-10">
                        <img class=" ml-auto mr-auto col-start-1 col-end-3" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto col-start-3 col-end-5" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto col-start-5 col-end-7" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto col-start-7 col-end-9" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto row-start-2 col-start-2 col-end-4" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto row-start-2 col-start-4 col-end-6" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto row-start-2 col-start-6 col-end-8" src="https://picsum.photos/200/100" alt="Company">
                    </div>
                </div>
                <div>
                    <p class=" text-2xl text-2023-red-dark font-bold">Bronze</p>
                    <div class="grid grid-cols-8 grid-rows-2 gap-4 justify-center align-center shadow-2xl shadow-2023-red-dark border border-black border-solid p-10">
                        <img class=" ml-auto mr-auto col-start-1 col-end-3" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto col-start-3 col-end-5" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto col-start-5 col-end-7" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto col-start-7 col-end-9" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto row-start-2 col-start-2 col-end-4" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto row-start-2 col-start-4 col-end-6" src="https://picsum.photos/200/100" alt="Company">
                        <img class=" ml-auto mr-auto row-start-2 col-start-6 col-end-8" src="https://picsum.photos/200/100" alt="Company">
                    </div>
                </div>
            </section>

            <!-- MAP -->
            <section class="bg-2023-orange p-10">
                <div class="flex align-center justify-center h-full w-full rounded overflow-hidden ">
                    <l-map v-model:zoom="zoom" :zoom="zoom" :minZoom="4" :maxZoom="18" :zoomAnimation="true"
                    :center="center" @click="addMarker" class="cursor-auto border border-black border-solid" :use-global-leaflet="false" style="height: 300px; width: 600px">
                        <l-tile-layer :url="url" :attribution="attribution" layer-type="base"/>
                        <l-marker :lat-lng="[41.17835293313974, -8.595830311142494]" style="background-color: red;">
                        <l-icon
                        :icon-anchor="staticAnchor"
                        >
                            <v-icon name="io-location-sharp" fill="#d94f04" scale="2"></v-icon>
                        </l-icon>
                        </l-marker>
                    </l-map>
                </div>
            </section>
        </main>
    </AppLayout>
</template>

<style>
</style>
