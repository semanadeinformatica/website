<script setup lang="ts">
import type { User } from "@/Types/User";

interface Props {
    item: User | undefined;
}

defineProps<Props>();

const socials: Record<string, { icon: string; color: string }> = {
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
                <template v-for="social in socials" :key="social">
                    <p v-if="item?.usertype?.social_media?.key">
                        <a
                            class="flex w-fit rounded-full p-2.5"
                            target="_blank"
                            :href="item?.usertype?.social_media?.key"
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
            <v-icon name="io-create" fill="2023-teal" scale="1.3"></v-icon>
        </button>
    </div>
</template>
