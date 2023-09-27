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
import type Session from "@/Types/Session";
import type { User } from "@/Types/User";
import EnrolledParticipants from "@/Components/Profile/EnrolledParticipants.vue";
import type Participant from "@/Types/Participant";

interface Props {
    confirmsTwoFactorAuthentication: boolean;
    sessions: Session[];
    user: User;
    participants: Participant[];
}

defineProps<Props>();
</script>

<template>
    <AppLayout title="Profile">
        <div class="flex flex-col items-center bg-2023-bg pt-6 sm:pt-0">
            <div
                class="relative m-6 min-h-screen w-full flex-col items-center border border-black p-6 md:max-w-[85vw]"
            >
                <div class="flex w-full justify-around max-md:flex-col">
                    <ProfilePicture :item="user ?? $page.props.auth.user" />
                    <InfoCard :item="user ?? $page.props.auth.user" />
                </div>
                <CvArea
                    v-if="
                        $page.props.auth.user?.usertype_type ===
                            'App\\Models\\Participant' ||
                        user?.usertype_type === 'App\\Models\\Participant'
                    "
                    :item="user ?? $page.props.auth.user"
                />

                <EnrolledParticipants
                    v-else-if="
                        $page.props.auth.user?.usertype_type ===
                            'App\\Models\\Company' ||
                        user?.usertype_type === 'App\\Models\\Company'
                    "
                    :participants="participants"
                />
            </div>
        </div>
    </AppLayout>
</template>
