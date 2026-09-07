<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Card from "@/Components/UI/Card.vue";
import SocialIcon from "@/Components/UI/SocialIcon.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ProfilePicture from "./ProfilePicture.vue";
import QRCode from "./QRCode.vue";
import {
    Award,
    Briefcase,
    Camera,
    Edit3,
    Mic,
    Shield,
    User as UserIcon,
} from "@lucide/vue";
import {
    type User,
    isAdmin,
    isParticipant,
    isCompany,
    isSpeaker,
} from "@/Types/User";
import type Company from "@/Types/Company";
import type Speaker from "@/Types/Speaker";
import type Participant from "@/Types/Participant";

interface Props {
    user?: User;
    isStaff?: boolean;
    points?: number | null;
    showSocialMedia?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    user: undefined,
    isStaff: false,
    points: undefined,
    showSocialMedia: true,
});

const page = usePage();

const socialKeys = [
    "facebook",
    "linkedin",
    "github",
    "twitter",
    "instagram",
    "website",
] as const;

const authUser = computed(() => page.props.auth?.user as User | undefined);

const isOwnProfile = computed(() =>
    Boolean(authUser.value?.id && authUser.value.id === props.user?.id),
);

const userSocialMedia = computed(() => {
    if (!props.user || isAdmin(props.user)) return undefined;
    return (
        props.user.usertype as
            | Company
            | Speaker
            | Participant
            | undefined
    )?.social_media;
});

const hasSocialMedia = computed(() => {
    if (!userSocialMedia.value) return false;
    return socialKeys.some((k) => Boolean(userSocialMedia.value?.[k]));
});

const userTypeBadge = computed(() => {
    if (props.isStaff) {
        return { label: "Staff", icon: Award };
    }
    if (props.user && isAdmin(props.user)) {
        return { label: "Administrador", icon: Shield };
    }
    if (props.user && isSpeaker(props.user)) {
        return { label: "Orador", icon: Mic };
    }
    if (props.user && isCompany(props.user)) {
        return { label: "Empresa", icon: Briefcase };
    }
    if (props.user && isParticipant(props.user)) {
        return { label: "Participante", icon: UserIcon };
    }
    return null;
});

const hasPoints = computed(
    () => props.points !== null && props.points !== undefined,
);

const hasDefaultActions = computed(() => isOwnProfile.value);
</script>

<template>
    <Card as="section" :interactive="false" padding="p-5 sm:p-7">
        <div
            class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center"
        >
            <!-- Left: Avatar + User details -->
            <div class="flex items-center gap-3.5 sm:gap-5">
                <slot name="avatar">
                    <ProfilePicture :item="user" size="sm" />
                </slot>

                <div class="min-w-0 space-y-1">
                    <slot name="details">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1
                                class="truncate text-base font-bold text-white sm:text-xl"
                            >
                                {{ user?.name }}
                            </h1>
                            <span
                                v-if="userTypeBadge"
                                class="pill-container gap-1.5 px-2.5 py-0.5 text-[11px] font-medium text-neutral-300"
                            >
                                <component
                                    :is="userTypeBadge.icon"
                                    :size="12"
                                    class="shrink-0 text-neutral-400"
                                />
                                <span>{{ userTypeBadge.label }}</span>
                            </span>
                        </div>

                        <p
                            v-if="user?.email"
                            class="truncate text-xs text-neutral-400 sm:text-sm"
                        >
                            {{ user.email }}
                        </p>

                        <!-- Social Media Links -->
                        <div
                            v-if="showSocialMedia && hasSocialMedia"
                            class="flex flex-wrap items-center gap-1.5 pt-1"
                        >
                            <template v-for="key in socialKeys" :key="key">
                                <a
                                    v-if="userSocialMedia?.[key]"
                                    class="flex h-7 w-7 items-center justify-center rounded-full border border-white/8 bg-white/5 text-neutral-400 transition-all duration-150 hover:border-white/20 hover:bg-white/10 hover:text-white active:scale-95"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :href="userSocialMedia[key]"
                                    :title="key"
                                >
                                    <SocialIcon :platform="key" :size="13" />
                                </a>
                            </template>
                        </div>
                    </slot>
                </div>
            </div>

            <!-- Right: Balance / Actions -->
            <div
                v-if="
                    $slots.actions ||
                    (isOwnProfile && (hasPoints || hasDefaultActions))
                "
                class="flex w-full flex-col gap-2.5 sm:w-auto sm:flex-row sm:items-center sm:justify-end"
            >
                <slot name="actions">
                    <!-- Balance / Points (Sinfrões) -->
                    <div
                        v-if="hasPoints && isOwnProfile"
                        class="pill-container justify-center sm:justify-start gap-2 px-3.5 py-1.5 text-xs font-medium text-neutral-300 shadow-none sm:text-sm self-stretch sm:self-auto"
                        title="O teu saldo de SINFrões"
                    >
                        <span class="text-neutral-400">Saldo:</span>
                        <span class="font-bold text-white">{{ points }}</span>
                        <img
                            class="h-4 w-4 object-contain"
                            alt="SINFrão"
                            title="SINFrão"
                            src="/images/sinf-2026-sm.svg"
                        />
                    </div>

                    <!-- Profile Actions -->
                    <div
                        v-if="hasDefaultActions && isOwnProfile"
                        class="flex flex-wrap items-center gap-2 w-full sm:w-auto"
                    >
                        <PrimaryButton
                            :href="route('profile.edit')"
                            color="pill"
                            padding="px-3.5 py-2 sm:px-4 sm:py-2"
                            class="flex-1 min-w-[130px] sm:min-w-0 sm:flex-initial sm:w-auto"
                        >
                            <Edit3 :size="15" />
                            <span>Editar Perfil</span>
                        </PrimaryButton>

                        <PrimaryButton
                            v-if="
                                isAdmin(user) ||
                                isStaff ||
                                (user && isCompany(user))
                            "
                            :href="route('user.scan-code')"
                            color="pill"
                            padding="px-3.5 py-2 sm:px-4 sm:py-2"
                            class="flex-1 min-w-[130px] sm:min-w-0 sm:flex-initial sm:w-auto"
                        >
                            <Camera :size="15" />
                            <span>Ler QR</span>
                        </PrimaryButton>

                        <QRCode
                            v-if="isParticipant(user) && user?.usertype"
                            :participant="user.usertype"
                            class="flex-1 min-w-[130px] sm:min-w-0 sm:flex-initial sm:w-auto"
                        />
                    </div>
                </slot>
            </div>
        </div>
    </Card>
</template>
