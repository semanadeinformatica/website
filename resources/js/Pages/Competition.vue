<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import Podium from "@/Components/Podium.vue";
</script>

<template>
    <AppLayout title="Competition">
        <Podium></Podium>
        <!-- RULES -->

        <section
            class="relative mt-5 grid grid-flow-row grid-cols-2 border-t border-black bg-2023-teal-dark text-justify max-lg:grid-flow-col max-lg:grid-cols-1 max-lg:grid-rows-2"
        >
            <h2
                class="absolute -top-9 left-1/2 -translate-x-1/2 transform border border-solid border-black bg-2023-red p-3 text-2xl font-bold text-white shadow-md shadow-2023-bg xl:text-3xl 2xl:text-4xl"
            >
                Regulamento
            </h2>

            <p class="p-20 text-lg text-white max-lg:pb-10">
                A Semana de Informática (SINF), organizada pelo Núcleo de
                Informática da Associação de Estudantes da Faculdade de
                Engenharia da Universidade do Porto (NIAEFEUP), foi criada com o
                intuito de permitir aos estudantes, independentemente do curso,
                desenvolver as suas capacidades nas diversas áreas da
                Informática, promovendo a sua interação com o mundo empresarial
                através de eventos sociais.
            </p>
            <p class="p-20 text-lg text-white max-lg:py-10">
                A edição de 2023 decorre entre os dias 25 e 31 de outubro. Os
                participantes têm a seu dispor palestras e workshops onde
                entrarão em contacto com diversas tecnologias e conceitos que
                não intervêm no percurso académico, focando-se portanto nas
                skills técnicas, assim como um pitch, sessões de entrevistas e
                visitas a empresas, fomentando o seu contacto com empresas de
                topo a nível nacional e internacional, podendo desenvolver as
                suas soft skills.
            </p>
        </section>

        <!-- PARTICIPATE -->
        <section
            class="relative flex content-center items-center justify-center py-24"
        >
            <h1
                class="absolute top-[1rem] m-4 flex justify-center p-3 text-4xl font-black text-2023-red"
            >
                Vamos a isto?
            </h1>

            <button
                class="relative mt-5 content-center justify-center border border-black bg-2023-teal-dark px-8 py-2 text-center text-2xl font-semibold text-white shadow-2023-orange transition-shadow hover:shadow-md active:shadow-none"
            >
                <p>Participar!</p>
            </button>
        </section>
    </AppLayout>
</template>
