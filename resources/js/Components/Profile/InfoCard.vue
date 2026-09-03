<script setup lang="ts">
import { type User, isAdmin, isParticipant, isCompany } from "@/Types/User";
import "vue-final-modal/style.css";
import { Link, usePage } from "@inertiajs/vue3";
import { Award, Camera, Edit3 } from "@lucide/vue";
import SocialIcon from "@/Components/UI/SocialIcon.vue";
import { route } from "ziggy-js";
import QRCode from "./QRCode.vue";

interface Props {
    user?: User;
    isStaff: boolean;
}

defineProps<Props>();

const page = usePage();

const socialKeys = [
    "facebook",
    "linkedin",
    "github",
    "twitter",
    "instagram",
    "website",
] as const;

const authUser = page.props.auth.user;
</script>

<template>
    <div
        class="bg-2025-blue text-text-color mr-3 flex justify-between rounded-lg border border-solid border-black p-3 shadow-xl shadow-black/80 max-md:mr-3 md:min-w-[30vw]"
    >
        <div class="flex flex-col items-start justify-between space-y-10">
            <div>
                <p class="flex items-center gap-1.5 font-bold">
                    <span>{{ user?.name }}</span>
                    <span
                        v-if="isStaff"
                        title="Staff"
                        class="inline-flex items-center text-amber-300"
                    >
                        <Award :size="18" />
                    </span>
                </p>
                <p>
                    {{ user?.email }}
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <template v-for="key in socialKeys" :key="key">
                    <a
                        v-if="
                            !isAdmin(user) &&
                            user?.usertype?.social_media?.[key]
                        "
                        class="flex w-fit items-center rounded-full text-white hover:opacity-80"
                        target="_blank"
                        :href="user.usertype.social_media[key]"
                    >
                        <SocialIcon :platform="key" :size="18" />
                    </a>
                </template>
            </div>
        </div>
        <div class="flex flex-col items-center justify-between">
            <Link
                v-if="$page.props.auth.user?.id === user?.id"
                :href="route('profile.edit')"
                class="flex items-center p-1 text-white hover:opacity-80"
            >
                <Edit3 :size="22" />
            </Link>
            <template
                v-if="
                    authUser?.id &&
                    authUser.id === user?.id &&
                    (isAdmin(user) || isStaff || isCompany(user))
                "
            >
                <Link
                    class="flex w-fit cursor-pointer items-center rounded-full p-1 text-white hover:opacity-80"
                    :href="route('user.scan-code')"
                >
                    <Camera :size="22" />
                </Link>
            </template>
            <QRCode
                v-if="
                    authUser?.id &&
                    authUser.id === user?.id &&
                    isParticipant(user) &&
                    user.usertype
                "
                :participant="user.usertype"
            />
        </div>
    </div>
</template>
