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
    participant: Participant;
    sessions: Session[];
    user: User;
    participants: Participant[];
    canCV: boolean;
}

defineProps<Props>();

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
};
</script>

<template>
    <AppLayout title="Profile">
        <div class="flex flex-col items-center bg-2023-bg pt-6 sm:pt-0">
            <div
                class="relative m-6 min-h-screen w-full flex-col items-center border border-black p-6 md:max-w-[85vw]"
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
                <CvArea
                    v-if="
                        $page.props.auth.user?.usertype_type ===
                            'App\\Models\\Participant' ||
                        (user?.usertype_type === 'App\\Models\\Participant' &&
                            canCV)
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
