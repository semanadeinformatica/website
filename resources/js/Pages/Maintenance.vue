<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Brevemente..." />
    <!-- TODO: localization -->
    <main class="min-h-screen bg-2023-bg">
        <div
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
            <p
                class="m-0 text-lg font-bold text-2023-teal max-[900px]:text-base"
            >
                Estamos a preparar mais uma edição da SINF!
            </p>
            <a
                href="https://2022.sinf.pt"
                class="relative m-0 text-lg font-bold text-2023-teal no-underline max-[900px]:text-base"
            >
                Revê a edição do último ano aqui!
                <img
                    class="absolute bottom-[-5px] right-[5px] w-3"
                    src="images/pointer.png"
                    alt="Pointer"
                />
            </a>
            <section
                class="relative mx-[25%] flex content-center items-center justify-center border border-solid border-black p-8"
            >
                <p
                    class="left-[calc(50% - 86.5px)] absolute -top-[30px] w-[165px] border border-solid border-black bg-2023-red p-2.5 px-5 text-lg font-bold text-white decoration-white shadow-md shadow-2023-bg"
                >
                    Contacta-nos!
                </p>
                <div
                    id="contacts-content-desktop"
                    class="flex w-full flex-row items-center justify-evenly gap-2.5 max-[900px]:hidden"
                >
                    <a
                        class="flex w-fit rounded-full bg-2023-red p-2"
                        target="_blank"
                        href="https://www.facebook.com/NIAEFEUP"
                    >
                        <v-icon name="io-logo-facebook" fill="white"></v-icon>
                    </a>
                    <a
                        class="flex w-fit rounded-full bg-2023-red-dark p-2"
                        target="_blank"
                        href="https://www.linkedin.com/company/nifeup/mycompany/"
                    >
                        <v-icon name="io-logo-linkedin" fill="white"></v-icon>
                    </a>
                    <p
                        class="text-lg font-bold text-2023-teal max-[900px]:text-base"
                    >
                        externas@sinf.pt
                    </p>
                    <a
                        class="flex w-fit rounded-full bg-2023-teal p-2"
                        target="_blank"
                        href="https://www.instagram.com/niaefeup/"
                    >
                        <v-icon name="io-logo-instagram" fill="white"></v-icon>
                    </a>
                    <a
                        class="flex w-fit rounded-full bg-2023-orange p-2"
                        target="_blank"
                        href="https://twitter.com/niaefeup"
                    >
                        <v-icon name="io-logo-twitter" fill="white"></v-icon>
                    </a>
                </div>
                <div
                    id="contacts-content-mobile"
                    class="hidden flex-col items-center gap-2.5 max-[900px]:flex"
                >
                    <p
                        class="text-lg font-bold text-2023-teal max-[900px]:text-base"
                    >
                        externas@sinf.pt
                    </p>
                    <div class="grid grid-cols-2 justify-center gap-2.5">
                        <a
                            class="flex w-fit rounded-full bg-2023-red p-2"
                            target="_blank"
                            href="https://www.facebook.com/NIAEFEUP"
                        >
                            <v-icon
                                name="io-logo-facebook"
                                fill="white"
                            ></v-icon>
                        </a>
                        <a
                            class="flex w-fit rounded-full bg-2023-red-dark p-2"
                            target="_blank"
                            href="https://www.linkedin.com/company/nifeup/mycompany/"
                        >
                            <v-icon
                                name="io-logo-linkedin"
                                fill="white"
                            ></v-icon>
                        </a>
                        <a
                            class="flex w-fit rounded-full bg-2023-teal p-2"
                            target="_blank"
                            href="https://www.instagram.com/niaefeup/"
                        >
                            <v-icon
                                name="io-logo-instagram"
                                fill="white"
                            ></v-icon>
                        </a>
                        <a
                            class="flex w-fit rounded-full bg-2023-orange p-2"
                            target="_blank"
                            href="https://twitter.com/niaefeup"
                        >
                            <v-icon
                                name="io-logo-twitter"
                                fill="white"
                            ></v-icon>
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
