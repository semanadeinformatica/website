<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";

const socialLinks = [
    {
        name: "Instagram",
        url: "https://www.instagram.com/sinffeup/",
        icon: "instagram",
    },
    {
        name: "LinkedIn",
        url: "https://pt.linkedin.com/company/sinffeup",
        icon: "linkedin",
    },
    {
        name: "Facebook",
        url: "https://www.facebook.com/sinffeup/",
        icon: "facebook",
    },
];

const socialItems: PillOption[] = socialLinks.map((social) => ({
    id: social.icon,
    href: social.url,
    ariaLabel: social.name,
    image: {
        src: `/images/${social.icon}.svg`,
        alt: social.name,
        class: "h-4 w-4 object-contain",
    },
}));

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="relative mt-auto overflow-hidden px-4 pt-16 pb-12 sm:px-6 sm:pt-20 sm:pb-14 lg:px-8"
    >
        <div
            class="pointer-events-none absolute inset-0 -z-10 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute inset-0 backdrop-blur-[2px]"
                style="
                    mask-image: linear-gradient(
                        to top,
                        rgba(0, 0, 0, 1) 0%,
                        rgba(0, 0, 0, 0) 100%
                    );
                    -webkit-mask-image: linear-gradient(
                        to top,
                        rgba(0, 0, 0, 1) 0%,
                        rgba(0, 0, 0, 0) 100%
                    );
                "
            />
            <div
                class="absolute inset-0 backdrop-blur-[6px]"
                style="
                    mask-image: linear-gradient(
                        to top,
                        rgba(0, 0, 0, 1) 0%,
                        rgba(0, 0, 0, 0) 75%
                    );
                    -webkit-mask-image: linear-gradient(
                        to top,
                        rgba(0, 0, 0, 1) 0%,
                        rgba(0, 0, 0, 0) 75%
                    );
                "
            />
            <div
                class="absolute inset-0 backdrop-blur-md"
                style="
                    mask-image: linear-gradient(
                        to top,
                        rgba(0, 0, 0, 1) 0%,
                        rgba(0, 0, 0, 0) 50%
                    );
                    -webkit-mask-image: linear-gradient(
                        to top,
                        rgba(0, 0, 0, 1) 0%,
                        rgba(0, 0, 0, 0) 50%
                    );
                "
            />
            <div
                class="absolute inset-0 backdrop-blur-xl"
                style="
                    mask-image: linear-gradient(
                        to top,
                        rgba(0, 0, 0, 1) 0%,
                        rgba(0, 0, 0, 0) 30%
                    );
                    -webkit-mask-image: linear-gradient(
                        to top,
                        rgba(0, 0, 0, 1) 0%,
                        rgba(0, 0, 0, 0) 30%
                    );
                "
            />
            <div
                class="absolute inset-0 bg-linear-to-t from-black/90 via-black/55 to-black/0"
            />
            <div
                class="absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-white/10 to-transparent"
            />
        </div>

        <div
            class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-8 md:flex-row"
        >
            <div class="flex flex-col items-center gap-3 md:items-start">
                <Link
                    :href="route('home')"
                    class="transition-opacity hover:opacity-80"
                >
                    <img
                        src="/images/sinf2026.svg"
                        alt="SINF 2026"
                        class="h-7 w-auto sm:h-8"
                    />
                </Link>
                <p class="text-center text-xs text-neutral-400 md:text-left">
                    Copyright &copy; {{ currentYear }}
                    <a
                        href="https://niaefeup.pt/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-white transition-colors"
                    >
                        NIAEFEUP </a
                    >. Todos os direitos reservados.
                </p>
            </div>

            <PillSelector :items="socialItems" size="sm" :wrap="false" />
        </div>
    </footer>
</template>
