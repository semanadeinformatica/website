<script setup lang="ts">
const contacts = [
    {
        icon: "io-logo-facebook",
        bg: "bg-2023-red",
        url: "https://www.facebook.com/NIAEFEUP",
    },
    {
        icon: "io-logo-linkedin",
        bg: "bg-2023-red-dark",
        url: "https://www.linkedin.com/company/nifeup/mycompany/",
    },
    {
        icon: "io-logo-instagram",
        bg: "bg-2023-teal",
        url: "https://www.instagram.com/niaefeup/",
    },
    { icon: "io-mail", bg: "bg-2023-orange", url: "mailto:ni@aefeup.pt" },
];
</script>

<template>
    <footer class="bg-2023-bg py-4">
        <section
            class="relative mx-[25%] flex content-center items-center justify-center p-8"
        >
            <div
                class="hidden w-full flex-row items-center justify-evenly gap-2 md:flex"
            >
                <template v-for="platform in contacts" :key="platform">
                    <a
                        class="flex w-fit rounded-full p-2.5"
                        :class="platform.bg"
                        target="_blank"
                        :href="platform.url"
                    >
                        <v-icon
                            :name="platform.icon"
                            fill="white"
                            scale="1.3"
                        ></v-icon>
                    </a>
                </template>
            </div>
            <div class="flex flex-col items-center gap-2 md:hidden">
                <div class="grid grid-cols-2 justify-center gap-2">
                    <template v-for="platform in contacts" :key="platform">
                        <a
                            class="flex w-fit rounded-full p-2.5"
                            :class="platform.bg"
                            target="_blank"
                            :href="platform.url"
                        >
                            <v-icon
                                :name="platform.icon"
                                fill="white"
                                scale="1.3"
                            ></v-icon>
                        </a>
                    </template>
                </div>
            </div>
        </section>
        <div class="container mx-auto text-center font-bold text-2023-teal">
            <p>Copyright &copy; 2023 NIAEFEUP, todos os direitos reservados</p>
        </div>
    </footer>
</template>

<style></style>
