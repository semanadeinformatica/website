<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProfilePicture from "@/Components/Profile/ProfilePicture.vue";
import InfoCard from "@/Components/Profile/InfoCard.vue";
import CvArea from "@/Components/Profile/CvArea.vue";
import InteractionArea from "@/Components/Profile/InteractionArea.vue";
import type Slot from "@/Types/Slot";
import { h } from "vue";
import TicketWrapper from "@/Components/Profile/TicketWrapper.vue";
import StickerWrapper from "@/Components/Profile/StickerWrapper.vue";
import type Session from "@/Types/Session";
import type { User } from "@/Types/User";
import EnrolledParticipants from "@/Components/Profile/EnrolledParticipants.vue";
import type Participant from "@/Types/Participant";

interface Props {
    confirmsTwoFactorAuthentication: boolean;
    tickets: Event[];
    slots: Slot[];
    sessions: Session[];
    user: User; // The user to render, can be the logged in user or another user
    participants: Participant[];
    canViewCV: boolean;
}

const { user } = defineProps<Props>();

const buttons = {
    ticket: {
        id: "ticket",
        title: "Bilhetes",
        component: h(TicketWrapper),
    },
    sticker: {
        id: "sticker",
        title: "Conquistas",
        component: h(StickerWrapper),
    },
    visitHistory: {
        id: "visitHistory",
        title: "Visitas",
        component: h(EnrolledParticipants),
    },
};
</script>

<template>
    <AppLayout title="Profile">
        <div class="flex flex-col items-center bg-2023-bg pt-6 sm:pt-0">
            <div
                class="relative m-6 min-h-screen w-full flex-col items-center p-6 md:max-w-[85vw]"
            >
                <div
                    class="relative m-6 min-h-screen w-full flex-col items-center p-6 md:max-w-[85vw]"
                >
                    <div class="flex w-full justify-around max-md:flex-col">
                        <ProfilePicture :item="user" />
                        <InfoCard :item="user" />
                    </div>
                    <CvArea
                        v-if="canViewCV"
                        :item="user"
                    />
                    <InteractionArea :buttons="buttons"> </InteractionArea>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
