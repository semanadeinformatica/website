<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import "vue3-carousel/dist/carousel.css";
import { Carousel, Slide, Pagination, Navigation } from "vue3-carousel";
import SpeakerSlide from "../Components/SpeakerSlide.vue";
import AppLayout from "../Layouts/AppLayout.vue";
import {
    LMap,
    LTileLayer,
    LMarker,
    LControl,
    LIcon,
} from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";

const carousel = ref(null);
let activeSpeaker = 1;

const emits = defineEmits(["update:modelValue"]);

const zoom = 17;
const center = [41.17835293313974, -8.595830311142494];
const url = "https://tile.openstreetmap.de/{z}/{x}/{y}.png";
const attribution =
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

const addMarker = (e) => {
    if (e.latlng !== undefined) {
        emits("update:modelValue", e.latlng);
    }
};
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
    modelValue: Object,
});
</script>

<template>
    <Head title="Home" />
    <AppLayout>
        <main class="bg-2023-bg">
            <!-- LOGO & DATE -->
            <section
                class="flex min-h-screen flex-col content-center items-center justify-evenly gap-[60px] py-[65px]"
            >
                <img
                    id="svg-1"
                    class="absolute left-[7%] w-[150px] animate-2023-maintenance-jump opacity-50 max-[900px]:hidden"
                    src="images/cy-sinf.svg"
                    alt="Stylized SINF logo"
                />
                <img
                    id="svg-2"
                    class="absolute right-[7%] top-[70px] w-[120px] animate-2023-maintenance-jump opacity-50 max-[900px]:hidden"
                    src="images/rc-sinf.svg"
                    alt="Stylized SINF logo"
                />
                <img
                    id="svg-3"
                    class="absolute bottom-[100px] right-[15%] w-[90px] animate-2023-maintenance-jump opacity-50 max-[900px]:hidden"
                    src="images/oc-sinf.svg"
                    alt="Stylized SINF logo"
                />

                <div class="relative">
                    <img
                        class="w-[450px] max-[900px]:w-[300px]"
                        src="images/sinf logo.png"
                        alt="Stylized SINF logo"
                    />
                    <span
                        class="margin-0 absolute bottom-[-20px] right-0 text-xl font-bold text-2023-teal"
                        >2023</span
                    >
                </div>
                <p
                    class="mr-2 border border-solid border-black p-2.5 px-8 text-lg font-bold text-2023-teal shadow-md shadow-2023-teal"
                >
                    semana_de_informática
                </p>
                <p class="margin-0 text-2xl font-bold text-2023-teal">
                    25 a 31 de outubro
                </p>
            </section>
            <!-- ABOUT US -->
            <section
                class="relative grid grid-flow-row grid-cols-2 border-t border-black bg-2023-teal-dark text-justify"
            >
                <h2
                    class="absolute -top-[36px] left-[calc(50%-89px)] border border-solid border-black bg-2023-red p-3 text-3xl font-bold text-white shadow-md shadow-2023-bg"
                >
                    Sobre nós
                </h2>
                <p class="p-20 text-lg text-white">
                    A Semana de Informática (SINF), organizada pelo Núcleo de
                    Informática da Associação de Estudantes da Faculdade de
                    Engenharia da Universidade do Porto (NIAEFEUP), foi criada
                    com o intuito de permitir aos estudantes, independentemente
                    do curso, desenvolver as suas capacidades nas diversas áreas
                    da Informática, promovendo a sua interação com o mundo
                    empresarial através de eventos sociais.
                </p>
                <p class="p-20 text-lg text-white">
                    A edição de 2022 decorre entre os dias 31 de outubro a 2 de
                    novembro. Os participantes têm a seu dispor palestras e
                    workshops onde entrarão em contacto com diversas tecnologias
                    e conceitos que não intervêm no percurso académico,
                    focando-se portanto nas skills técnicas, assim como um
                    pitch, sessões de entrevistas e visitas a empresas,
                    fomentando o seu contacto com empresas de topo a nível
                    nacional e internacional, podendo desenvolver as suas soft
                    skills.
                </p>
            </section>
            <!-- CALL TO ACTION -->
            <section class="relative flex py-20">
                <h2
                    class="absolute left-[calc(10%+70px)] top-[54px] border border-solid border-black bg-2023-orange p-2 text-2xl font-bold text-white shadow-md shadow-2023-teal-dark"
                >
                    Inscreve-te!
                </h2>
                <div
                    class="mx-[10%] flex w-full justify-around border border-solid border-black p-12 text-xl font-bold text-2023-teal shadow-2xl shadow-2023-orange"
                >
                    <span>07 dias</span>
                    <span>70 bancas</span>
                    <span>7 talks</span>
                    <span>7 workshops</span>
                </div>
            </section>
            <!-- SPEAKERS -->
            <section class="relative h-max min-h-max pb-20">
                <p
                    class="absolute left-[calc(50%-64px)] top-0 w-min self-center border border-solid border-black bg-2023-red-dark p-3 text-2xl font-bold text-white shadow shadow-2023-bg"
                >
                    Speakers
                </p>
                <div class="h-max min-h-max pt-20">
                    <Carousel
                        @slide-start="handleSlideStart"
                        :items-to-show="4"
                        :wrap-around="true"
                        ref="carousel"
                    >
                        <Slide v-for="slide in 10" :key="slide">
                            <SpeakerSlide
                                :slide_id="{ slide }"
                                :currentSlide="{ activeSpeaker }"
                            />
                        </Slide>
                    </Carousel>
                </div>
                <div class="align-center flex justify-center gap-3">
                    <button @click="prev">
                        <v-icon
                            name="fa-arrow-left"
                            fill="#007172"
                            scale="2"
                        ></v-icon>
                    </button>
                    <button @click="next">
                        <v-icon
                            name="fa-arrow-right"
                            fill="#007172"
                            scale="2"
                        ></v-icon>
                    </button>
                </div>
            </section>
            <!-- SPONSORS -->
            <section class="relative flex flex-col gap-10 px-20 py-24">
                <p
                    class="absolute left-[calc(50%-64px)] top-0 w-min self-center border border-solid border-black bg-2023-teal-dark p-3 text-2xl font-bold text-white shadow shadow-2023-bg"
                >
                    Sponsors
                </p>
                <div>
                    <p class="text-2xl font-bold text-2023-orange">Gold</p>
                    <div
                        class="row flex justify-around border border-solid border-black p-10 shadow-2xl shadow-2023-orange"
                    >
                        <img
                            src="https://picsum.photos/300/200"
                            alt="Company"
                        />
                        <img
                            src="https://picsum.photos/300/200"
                            alt="Company"
                        />
                        <img
                            src="https://picsum.photos/300/200"
                            alt="Company"
                        />
                    </div>
                </div>
                <div>
                    <p class="text-2xl font-bold text-2023-teal-dark">Silver</p>
                    <div
                        class="grid grid-cols-8 grid-rows-2 gap-4 border border-solid border-black p-10 shadow-2xl shadow-2023-teal-dark"
                    >
                        <img
                            class="col-start-1 col-end-3 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-3 col-end-5 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-5 col-end-7 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-7 col-end-9 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-2 col-end-4 row-start-2 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-4 col-end-6 row-start-2 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-6 col-end-8 row-start-2 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                    </div>
                </div>
                <div>
                    <p class="text-2xl font-bold text-2023-red-dark">Bronze</p>
                    <div
                        class="align-center grid grid-cols-8 grid-rows-2 justify-center gap-4 border border-solid border-black p-10 shadow-2xl shadow-2023-red-dark"
                    >
                        <img
                            class="col-start-1 col-end-3 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-3 col-end-5 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-5 col-end-7 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-7 col-end-9 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-2 col-end-4 row-start-2 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-4 col-end-6 row-start-2 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                        <img
                            class="col-start-6 col-end-8 row-start-2 ml-auto mr-auto"
                            src="https://picsum.photos/200/100"
                            alt="Company"
                        />
                    </div>
                </div>
            </section>

            <!-- MAP -->
            <section class="bg-2023-orange p-10">
                <div
                    class="align-center flex h-full w-full justify-center overflow-hidden rounded"
                >
                    <l-map
                        v-model:zoom="zoom"
                        :zoom="zoom"
                        :minZoom="4"
                        :maxZoom="18"
                        :zoomAnimation="true"
                        :center="center"
                        @click="addMarker"
                        class="cursor-auto border border-solid border-black"
                        :use-global-leaflet="false"
                        style="height: 300px; width: 600px"
                    >
                        <l-tile-layer
                            :url="url"
                            :attribution="attribution"
                            layer-type="base"
                        />
                        <l-marker
                            :lat-lng="[41.17835293313974, -8.595830311142494]"
                            style="background-color: red"
                        >
                            <l-icon :icon-anchor="staticAnchor">
                                <v-icon
                                    name="io-location-sharp"
                                    fill="#d94f04"
                                    scale="2"
                                ></v-icon>
                            </l-icon>
                        </l-marker>
                    </l-map>
                </div>
            </section>
        </main>
    </AppLayout>
</template>

<style></style>
