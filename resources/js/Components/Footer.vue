<script setup lang="ts">
import { OhVueIcon } from "oh-vue-icons";

const contacts = [
    {
        icon: "io-logo-facebook",
        bg: "bg-2023-red",
        url: "https://www.facebook.com/sinfFEUP/",
    },
    {
        icon: "io-logo-linkedin",
        bg: "bg-2023-red-dark",
        url: "https://pt.linkedin.com/company/sinffeup",
    },
    {
        icon: "io-logo-instagram",
        bg: "bg-2023-teal",
        url: "https://www.instagram.com/sinffeup/?hl=en",
    },
    { icon: "io-mail", bg: "bg-2023-orange", url: "mailto:geral@sinf.pt" },
];
</script>

<template>
    <footer class="bg-2023-bg py-4">
        <section
            class="relative flex content-center items-center justify-center p-8 md:mx-[30%] lg:mx-[35%]"
        >
            <div
                class="hidden w-full flex-row items-center justify-evenly md:flex"
            >
                <template v-for="platform in contacts" :key="platform">
                    <a
                        class="flex w-fit rounded-full p-2.5"
                        :class="platform.bg"
                        target="_blank"
                        :href="platform.url"
                    >
                        <OhVueIcon
                            :name="platform.icon"
                            fill="white"
                            scale="1.3"
                        ></OhVueIcon>
                    </a>
                </template>
            </div>
            <div class="flex flex-col items-center gap-2 md:hidden">
                <div class="grid grid-cols-2 justify-center gap-3">
                    <template v-for="platform in contacts" :key="platform">
                        <a
                            class="flex w-fit rounded-full p-2.5"
                            :class="platform.bg"
                            target="_blank"
                            :href="platform.url"
                        >
                            <OhVueIcon
                                :name="platform.icon"
                                fill="white"
                                scale="1.3"
                            ></OhVueIcon>
                        </a>
                    </template>
                </div>
            </div>
        </section>
        <div class="container mx-auto text-center font-bold text-2023-teal">
            <p>
                Copyright &copy; 2023
                <a href="https://ni.fe.up.pt/">NIAEFEUP</a>, todos os direitos
                reservados
            </p>
        </div>
    </footer>
</template>

<style></style>
