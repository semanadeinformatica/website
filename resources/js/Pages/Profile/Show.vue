<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProfilePicture from "@/Components/Profile/ProfilePicture.vue";
import InfoCard from "@/Components/Profile/InfoCard.vue";
import CvArea from "@/Components/Profile/CvArea.vue";
/*import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";*/
import type Participant from "@/Types/Participant";
import InteractionArea from "@/Components/Profile/InteractionArea.vue";
import type Slot from "@/Types/Slot";
import { computed } from "vue";

interface Props {
    confirmsTwoFactorAuthentication: boolean;
    tickets: Event[];
    slots: Slot[];
    participant: Participant;
}

defineProps<Props>();

const buttons = computed(() => {
    return {
        tickets: {
            id: "ticket",
            title: "Bilhetes",
        },
        stickers: {
            id: "sticker",
            title: "Conquistas",
        },
    };
});
</script>

<template>
    <AppLayout title="Profile">
        <div class="flex flex-col items-center bg-2023-bg pt-6 sm:pt-0">
            <template
                v-if="
                    participant ||
                    $page.props.auth.user?.usertype_type ===
                        'App\\Models\\Participant'
                "
            >
                <div
                    class="relative m-6 min-h-screen w-full flex-col items-center p-6 md:max-w-[85vw]"
                >
                    <div class="flex w-full justify-around max-md:flex-col">
                        <ProfilePicture
                            :item="
                                participant
                                    ? participant.user
                                    : $page.props.auth.user
                            "
                        />
                        <InfoCard
                            :item="
                                participant
                                    ? participant.user
                                    : $page.props.auth.user
                            "
                        />
                    </div>
                    <CvArea
                        :item="
                            participant
                                ? participant.user
                                : $page.props.auth.user
                        "
                    />
                    <InteractionArea :buttons="buttons"> </InteractionArea>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
