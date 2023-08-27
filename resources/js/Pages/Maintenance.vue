<script setup>
</script>

<template>
  <main class="min-h-screen bg-2023-bg">
    <div class="flex flex-col gap-[60px] py-[65px] justify-evenly items-center content-center min-h-screen">
                <img id="svg-1" class="animate-2023-maintenance-jump opacity-50 absolute max-[900px]:hidden w-[150px] left-[7%]" src="images/cy-sinf.svg" alt="Stylized SINF logo">
                <img id="svg-2" class="animate-2023-maintenance-jump opacity-50 absolute max-[900px]:hidden top-[70px] w-[120px] right-[7%]" src="images/rc-sinf.svg" alt="Stylized SINF logo">
                <img id="svg-3" class="animate-2023-maintenance-jump opacity-50 absolute max-[900px]:hidden bottom-[100px] w-[90px] right-[15%]" src="images/oc-sinf.svg" alt="Stylized SINF logo">
                
                <div class="relative">
                    <img class="w-[450px] max-[900px]:w-[300px]" src="images/sinf logo.png" alt="Stylized SINF logo">
                    <span class="text-2023-teal text-xl font-bold absolute bottom-[-20px] right-0 margin-0">2023</span>
                </div>
                <p class="shadow-md shadow-2023-teal text-2023-teal p-2.5 border border-solid border-black px-8 text-lg font-bold mr-2">semana_de_informática</p>
                <p class="text-2023-teal margin-0 text-2xl font-bold">25 a 31 de outubro</p>
                <p class="text-2023-teal text-lg font-bold m-0 max-[900px]:text-base">Estamos a preparar mais uma edição da SINF!</p>
                <a href="https://sinf.pt" class="text-2023-teal no-underline text-lg font-bold m-0 max-[900px]:text-base relative">
                    Revê a edição do último ano aqui!
                    <img class="w-3 absolute bottom-[-5px] right-[5px]" src="images/pointer.png" alt="Pointer">
                </a>
                <section class="flex content-center items-center justify-center relative p-8 border border-black border-solid mx-[25%]">
                    <p class="bg-2023-red shadow-md shadow-2023-bg text-white p-2.5 px-5 decoration-white text-lg border border-solid border-black font-bold w-[165px] absolute -top-[30px] left-[calc(50% - 86.5px)]">Contacta-nos!</p>
                    <div class="flex gap-2.5 flex-row items-center w-full justify-evenly max-[900px]:hidden" id="contacts-content-desktop">
                        <a class="bg-2023-red p-2 rounded-full flex w-fit" target="_blank" href="https://www.facebook.com/NIAEFEUP">
                            <v-icon name="io-logo-facebook" fill="white"></v-icon>
                        </a>
                        <a class="bg-2023-red-dark p-2 rounded-full flex w-fit" target="_blank" href="https://www.linkedin.com/company/nifeup/mycompany/">
                            <v-icon name="io-logo-linkedin" fill="white"></v-icon>
                        </a>
                        <p class="text-2023-teal text-lg font-bold max-[900px]:text-base">externas@sinf.pt</p>
                        <a class="bg-2023-teal p-2 rounded-full flex w-fit" target="_blank" href="https://www.instagram.com/niaefeup/">
                            <v-icon name="io-logo-instagram" fill="white"></v-icon>
                        </a>
                        <a class="bg-2023-orange p-2 rounded-full flex w-fit" target="_blank" href="https://twitter.com/niaefeup">
                            <v-icon name="io-logo-twitter" fill="white"></v-icon>
                        </a>
                    </div>
                    <div class="hidden flex-col items-center gap-2.5 max-[900px]:flex" id="contacts-content-mobile">
                        <p class="text-2023-teal text-lg font-bold max-[900px]:text-base">externas@sinf.pt</p>
                        <div class="grid grid-cols-2 gap-2.5 justify-center">
                            <a class="bg-2023-red p-2 rounded-full flex w-fit" target="_blank" href="https://www.facebook.com/NIAEFEUP">
                                <v-icon name="io-logo-facebook" fill="white"></v-icon>
                            </a>
                            <a class="bg-2023-red-dark p-2 rounded-full flex w-fit" target="_blank" href="https://www.linkedin.com/company/nifeup/mycompany/">
                                <v-icon name="io-logo-linkedin" fill="white"></v-icon>
                            </a>
                            <a class="bg-2023-teal p-2 rounded-full flex w-fit" target="_blank" href="https://www.instagram.com/niaefeup/">
                                <v-icon name="io-logo-instagram" fill="white"></v-icon>
                            </a>
                            <a class="bg-2023-orange p-2 rounded-full flex w-fit" target="_blank" href="https://twitter.com/niaefeup">
                                <v-icon name="io-logo-twitter" fill="white"></v-icon>
                            </a>    
                        </div>
                    </div>
                </section>    
            </div>
  </main>
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