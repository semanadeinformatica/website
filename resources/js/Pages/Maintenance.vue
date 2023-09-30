<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
</script>

<template>
    <Head :title="$t('pages.maintenance')" />
    <main class="min-h-screen bg-2023-bg">
        <div
            class="flex min-h-screen flex-col content-center items-center justify-evenly gap-14 py-16"
        >
            <img
                id="svg-1"
                class="absolute left-[7%] w-36 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="images/cy-sinf.svg"
                :alt="$t('general.sinfLogoAlt')"
            />
            <img
                id="svg-2"
                class="absolute right-[7%] top-16 w-32 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="images/rc-sinf.svg"
                :alt="$t('general.sinfLogoAlt')"
            />
            <img
                id="svg-3"
                class="absolute bottom-28 right-[15%] w-20 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="images/oc-sinf.svg"
                :alt="$t('general.sinfLogoAlt')"
            />

            <div class="relative">
                <img
                    class="w-96 max-ml:w-72"
                    src="images/sinf logo.png"
                    :alt="$t('general.sinfLogoAlt')"
                />
                <span
                    class="margin-0 absolute -bottom-5 right-0 text-xl font-bold text-2023-teal"
                    >{{ 2023 }}</span
                >
            </div>
            <p
                class="mr-2 border border-solid border-black p-2.5 px-8 text-lg font-bold text-2023-teal shadow-md shadow-2023-teal"
            >
                {{ $t("semana_de_informática") }}
            </p>
            <p class="margin-0 text-center text-2xl font-bold text-2023-teal">
                {{ $t("pages.maintenance.date") }}
            </p>
            <p
                class="m-0 text-center text-lg font-bold text-2023-teal max-ml:text-base"
            ></p>
            <a
                href="https://2022.sinf.pt"
                class="relative m-0 text-center text-lg font-bold text-2023-teal underline max-ml:text-base"
            >
                {{ $t("pages.maintenance.prepareEdition") }}
            </a>
            <section
                class="relative mx-[25%] flex content-center items-center justify-center border border-solid border-black p-8"
            >
                <p
                    class="left-[calc(50% - 86.5px)] absolute -top-[30px] w-max border border-solid border-black bg-2023-red p-2.5 px-5 text-lg font-bold text-white decoration-white shadow-md shadow-2023-bg"
                >
                    {{ $t("pages.maintenance.contactUs") }}
                </p>
                <div
                    id="contacts-content-desktop"
                    class="flex w-full flex-row items-center justify-evenly gap-2.5 max-ml:hidden"
                >
                    <a
                        class="flex w-fit rounded-full bg-2023-red p-2"
                        target="_blank"
                        href="https://www.facebook.com/NIAEFEUP"
                    >
                        <OhVueIcon
                            name="io-logo-facebook"
                            fill="white"
                        ></OhVueIcon>
                    </a>
                    <a
                        class="flex w-fit rounded-full bg-2023-red-dark p-2"
                        target="_blank"
                        href="https://www.linkedin.com/company/nifeup/mycompany/"
                    >
                        <OhVueIcon
                            name="io-logo-linkedin"
                            fill="white"
                        ></OhVueIcon>
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
                        <OhVueIcon
                            name="io-logo-instagram"
                            fill="white"
                        ></OhVueIcon>
                    </a>
                    <a
                        class="flex w-fit rounded-full bg-2023-orange p-2"
                        target="_blank"
                        href="https://twitter.com/niaefeup"
                    >
                        <OhVueIcon
                            name="io-logo-twitter"
                            fill="white"
                        ></OhVueIcon>
                    </a>
                </div>
                <div
                    id="contacts-content-mobile"
                    class="hidden flex-col items-center gap-2.5 max-ml:flex"
                >
                    <p
                        class="text-lg font-bold text-2023-teal max-ml:text-base"
                    >
                        externas@sinf.pt
                    </p>
                    <div class="grid grid-cols-2 justify-center gap-2.5">
                        <a
                            class="flex w-fit rounded-full bg-2023-red p-2"
                            target="_blank"
                            href="https://www.facebook.com/NIAEFEUP"
                        >
                            <OhVueIcon
                                name="io-logo-facebook"
                                fill="white"
                            ></OhVueIcon>
                        </a>
                        <a
                            class="flex w-fit rounded-full bg-2023-red-dark p-2"
                            target="_blank"
                            href="https://www.linkedin.com/company/nifeup/mycompany/"
                        >
                            <OhVueIcon
                                name="io-logo-linkedin"
                                fill="white"
                            ></OhVueIcon>
                        </a>
                        <a
                            class="flex w-fit rounded-full bg-2023-teal p-2"
                            target="_blank"
                            href="https://www.instagram.com/niaefeup/"
                        >
                            <OhVueIcon
                                name="io-logo-instagram"
                                fill="white"
                            ></OhVueIcon>
                        </a>
                        <a
                            class="flex w-fit rounded-full bg-2023-orange p-2"
                            target="_blank"
                            href="https://twitter.com/niaefeup"
                        >
                            <OhVueIcon
                                name="io-logo-twitter"
                                fill="white"
                            ></OhVueIcon>
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
