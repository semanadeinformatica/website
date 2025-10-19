<script setup lang="ts">
import { type User, isAdmin, isParticipant, isCompany } from "@/Types/User";
import { OhVueIcon } from "oh-vue-icons";
import "vue-final-modal/style.css";
import { Link, usePage } from "@inertiajs/vue3";
import route from "ziggy-js";
import QRCode from "./QRCode.vue";

interface Props {
    user?: User;
    isStaff: boolean;
}

defineProps<Props>();

const page = usePage();

const socials = {
    facebook: {
        icon: "io-logo-facebook",
        color: "white",
    },
    linkedin: {
        icon: "io-logo-linkedin",
        color: "white",
    },
    github: {
        icon: "io-logo-github",
        color: "white",
    },
    twitter: {
        icon: "io-logo-twitter",
        color: "white",
    },
    instagram: {
        icon: "io-logo-instagram",
        color: "white",
    },
    website: {
        icon: "io-logo-globe",
        color: "white",
    },
};

const iconColor: Record<string, string> = {
    orange: "#f29325",
    "teal-dark": "#025259",
    "red-dark": "#b15d5d",
    red: "#d94f04",
    teal: "#007172",
};

const authUser = page.props.auth.user;
</script>

<template>
    <div
        class="rounded-lg flex justify-between border border-solid border-black bg-2025-blue p-3 text-text-color shadow-xl shadow-black/80 mr-3 max-md:mr-3 md:min-w-[30vw]"
    >
        <div class="flex flex-col justify-between items-start space-y-10">
            <div>
                <p class="font-bold">
                    {{ user?.name }}
                    <span v-if="isStaff" title="Staff">
                        <OhVueIcon
                            name="io-ribbon-outline"
                            scale="1.4"
                        ></OhVueIcon>
                    </span>
                </p>
                <p>
                    {{ user?.email }}
                </p>
            </div>
            <div class="flex space-x-4">
                <template v-for="(social, key) in socials" :key="key">
                    <a
                        v-if="
                            !isAdmin(user) &&
                            user?.usertype?.social_media?.[key]
                        "
                        class="flex w-fit rounded-full"
                        target="_blank"
                        :href="user.usertype.social_media[key]"
                    >
                        <OhVueIcon
                            :name="social.icon"
                            scale="1"
                            :fill="iconColor[social.color]"
                        ></OhVueIcon>
                    </a>
                </template>
            </div>
        </div>
        <div class="flex flex-col items-center justify-between">
            <Link
                v-if="$page.props.auth.user?.id == user?.id"
                :href="route('profile.edit')"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-8 text-text-color"
                    viewBox="0 0 512 512"
                >
                    <path
                        fill="none"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="24"
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
            </Link>
            <template v-if="(authUser.id && authUser.id === user.id) && (isAdmin(user) || isStaff || isCompany(user))">
                <!-- TODO: this does not bring problems to us because we can only see other people's profiles if we are admins or companies (under certain conditions) which already would have the scan button enabled -->
                <Link
                    class="flex w-fit cursor-pointer rounded-full text-white"
                    :href="route('user.scan-code')"
                >
                    <OhVueIcon name="io-camera" scale="1"></OhVueIcon>
                </Link>
            </template>
            <QRCode
                v-if="(authUser.id && authUser.id === user.id) && (isParticipant(user) && user.usertype)"
                :participant="user.usertype"
            ></QRCode>
        </div>
    </div>
</template>
