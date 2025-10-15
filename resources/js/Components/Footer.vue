<script setup lang="ts">
import { OhVueIcon } from "oh-vue-icons";
import PortoTechBanner from "@/Components/Footer/PortoTechBanner.vue";

const contacts = [
    {
        social: "facebook",
        url: "https://www.facebook.com/sinfFEUP/",
    },
    {
        social: "linkedin",
        url: "https://pt.linkedin.com/company/sinffeup",
    },
    {
        social: "instagram",
        url: "https://www.instagram.com/sinffeup/?hl=en",
    },
    {
        social: "web",
        url: "mailto:geral@sinf.pt",
    }
];
</script>

<template>
    <footer class="bg-2025-blue-dark py-8">
        <section
            class="relative flex content-center items-center justify-center p-8 md:mx-[30%] lg:mx-[35%]"
        >
            <div
                class="hidden w-full flex-row items-center justify-evenly md:flex"
            >
                <template v-for="platform in contacts" :key="platform">
                    <a
                        class="flex w-fit rounded-full p-2.5"
                        target="_blank"
                        :href="platform.url"
                    >
                        <img :src="`/images/${platform.social}.svg`" class="h-20 w-20 hover:scale-105
           [filter:drop-shadow(0_0_0_rgba(0,0,0,0))]
           hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]"/>
                    </a>
                </template>
            </div>
            <div class="flex flex-col items-center gap-2 md:hidden">
                <div class="grid grid-cols-2 justify-center gap-3">
                    <template v-for="platform in contacts" :key="platform">
                        <a
                            class="flex w-fit rounded-full p-2.5"
                            target="_blank"
                            :href="platform.url"
                        >
                            <img :src="`/images/${platform.social}.svg`" class="h-20 w-20"/>
                        </a>
                    </template>
                </div>
            </div>
        </section>
        <!-- <PortoTechBanner /> -->
        <div class="container mx-auto text-center font-bold text-text-color">
            <p>
                Copyright &copy; 2025
                <a href="https://ni.fe.up.pt/">NIAEFEUP</a>, todos os direitos
                reservados
            </p>
        </div>
    </footer>
</template>

<style></style>
