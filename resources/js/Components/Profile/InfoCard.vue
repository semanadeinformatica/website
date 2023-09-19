<script setup lang="ts">
import type { User } from "@/Types/User";

interface Props {
    item: User | undefined;
}

defineProps<Props>();

const socials = {
    facebook: {
        icon: "io-logo-facebook",
        color: "bg-2023-red",
    },
    linkedin: {
        icon: "io-logo-linkedin",
        color: "bg-2023-red-dark",
    },
    github: {
        icon: "io-logo-github",
        color: "bg-2023-red",
    },
    twitter: {
        icon: "io-logo-twitter",
        color: "bg-2023-red-dark",
    },
    instagram: {
        icon: "io-logo-instagram",
        color: "bg-2023-red",
    },
    website: {
        icon: "io-logo-globe",
        color: "bg-2023-red-dark",
    },
};
</script>

<template>
    <div
        class="m-4 flex justify-between border border-solid border-black bg-2023-bg p-3 text-2023-teal shadow-xl shadow-2023-red-dark md:min-w-[30vw]"
    >
        <div class="flex-col">
            <div>
                <p class="font-bold">{{ item?.name }}</p>
                <p>
                    {{ item?.email }}
                </p>
            </div>
            <div class="mt-10 flex">
                <template v-for="(social, key) in socials" :key="social">
                    <p
                        v-if="
                            item?.usertype_type !== 'App\\Models\\Admin' &&
                            item?.usertype?.social_media?.[key]
                        "
                    >
                        <a
                            class="flex w-fit rounded-full p-2.5"
                            target="_blank"
                            :href="item?.usertype?.social_media?.[key]"
                        >
                            <v-icon
                                :name="social.icon"
                                :fill="social.color"
                                scale="1.3"
                            ></v-icon>
                        </a>
                    </p>
                </template>
            </div>
        </div>
        <button v-if="$page.props.auth.user.id == item?.id" class="self-start">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-8 text-2023-teal"
                viewBox="0 0 512 512"
            >
                <path
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="36"
                    d="M384 224v184a40 40 0 01-40 40H104a40 40 0 01-40-40V168a40 40 0 0140-40h167.48"
                />
                <path
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="24"
                    d="M459.94 53.25a16.06 16.06 0 00-23.22-.56L424.35 65a8 8 0 000 11.31l11.34 11.32a8 8 0 0011.34 0l12.06-12c6.1-6.09 6.67-16.01.85-22.38zM399.34 90L218.82 270.2a9 9 0 00-2.31 3.93L208.16 299a3.91 3.91 0 004.86 4.86l24.85-8.35a9 9 0 003.93-2.31L422 112.66a9 9 0 000-12.66l-9.95-10a9 9 0 00-12.71 0z"
                />
            </svg>
        </button>
    </div>
</template>
