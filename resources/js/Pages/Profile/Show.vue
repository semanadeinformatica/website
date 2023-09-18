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
import type Student from "@/Types/Student";

interface Props {
    confirmsTwoFactorAuthentication: boolean;
    sessions: Session[];
    student: Student;
}

defineProps<Props>();
</script>

<template>
    <AppLayout title="Profile">
        <main class="flex flex-col items-center bg-2023-bg pt-6 sm:pt-0">
            <template
                v-if="
                    student ||
                    $page.props.auth.user.usertype_type ===
                        'App\\Models\\Student'
                "
            >
                <div
                    class="relative m-6 w-full flex-col items-center border border-black p-6 md:max-w-[85vw]"
                >
                    <ModalsContainer />
                    <div class="flex w-full justify-around max-md:flex-col">
                        <ProfilePicture
                            :item="
                                student ? student.user : $page.props.auth.user
                            "
                        />
                        <InfoCard
                            :item="
                                student ? student.user : $page.props.auth.user
                            "
                        />
                    </div>
                    <CvArea
                        :item="student ? student.user : $page.props.auth.user"
                    />
                </div>
            </template>
        </main>
    </AppLayout>
</template>
