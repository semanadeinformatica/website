<script setup lang="ts">
import AppLayout from "../Layouts/AppLayout.vue";
import SpeakersCarousel from "../Components/Home/SpeakersCarousel.vue";
import Map from "../Components/Home/Map.vue";
import SponsorBanner from "../Components/Home/SponsorBanner.vue";
</script>

<template>
    <AppLayout title="Home">
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
                class="relative grid grid-flow-row grid-cols-2 border-t border-black bg-2023-teal-dark text-justify max-lg:grid-flow-col max-lg:grid-cols-1 max-lg:grid-rows-2"
            >
                <h2
                    class="absolute -top-[36px] left-[calc(50%-88.2415px)] border border-solid border-black bg-2023-red p-3 text-3xl font-bold text-white shadow-md shadow-2023-bg"
                >
                    Sobre nós
                </h2>
                <p class="p-20 text-lg text-white max-lg:pb-10">
                    A Semana de Informática (SINF), organizada pelo Núcleo de
                    Informática da Associação de Estudantes da Faculdade de
                    Engenharia da Universidade do Porto (NIAEFEUP), foi criada
                    com o intuito de permitir aos estudantes, independentemente
                    do curso, desenvolver as suas capacidades nas diversas áreas
                    da Informática, promovendo a sua interação com o mundo
                    empresarial através de eventos sociais.
                </p>
                <p class="p-20 text-lg text-white max-lg:py-10">
                    A edição de 2023 decorre entre os dias 25 e 31 de outubro.
                    Os participantes têm a seu dispor palestras e workshops onde
                    entrarão em contacto com diversas tecnologias e conceitos
                    que não intervêm no percurso académico, focando-se portanto
                    nas skills técnicas, assim como um pitch, sessões de
                    entrevistas e visitas a empresas, fomentando o seu contacto
                    com empresas de topo a nível nacional e internacional,
                    podendo desenvolver as suas soft skills.
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
                    class="mx-[10%] grid w-full grid-cols-4 gap-4 border border-solid border-black p-12 text-xl font-bold text-2023-teal shadow-2xl shadow-2023-orange max-lg:grid-cols-2"
                >
                    <span class="text-center">7 dias</span>
                    <span class="text-center">70 bancas</span>
                    <span class="text-center">4 talks</span>
                    <span class="text-center">1 workshops</span>
                </div>
            </section>
            <!-- SPEAKERS -->
            <section class="grid-rows-[repeat(3, 1fr)] grid grid-cols-1 gap-10">
                <p
                    class="mr-[5px] flex w-fit place-self-center border border-solid border-black bg-2023-red-dark p-3 text-2xl font-bold text-white shadow shadow-2023-bg"
                >
                    Speakers
                </p>
                <SpeakersCarousel></SpeakersCarousel>
            </section>
            <!-- SPONSORS -->
            <section class="flex flex-col gap-10 px-20 py-24">
                <p
                    class="mr-[5px] flex w-min place-self-center border border-solid border-black bg-2023-teal-dark p-3 text-2xl font-bold text-white shadow shadow-2023-bg"
                >
                    Sponsors
                </p>
                <SponsorBanner tier="plat"></SponsorBanner>
                <SponsorBanner tier="gold"></SponsorBanner>
                <SponsorBanner tier="silver"></SponsorBanner>
            </section>
            <!-- MAP -->
            <section class="bg-2023-orange p-10">
                <Map></Map>
            </section>
        </main>
    </AppLayout>
</template>

<style>
#svg-1 {
    animation-delay: -0.5s;
}

#svg-2 {
    animation-delay: -1s;
}

#svg-3 {
    animation-delay: -0.8s;
}
</style>
