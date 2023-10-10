<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProfilePicture from "@/Components/Profile/ProfilePicture.vue";
import InfoCard from "@/Components/Profile/InfoCard.vue";
import CvArea from "@/Components/Profile/CvArea.vue";
import InteractionArea from "@/Components/Profile/InteractionArea.vue";
import type Slot from "@/Types/Slot";
import { computed, h } from "vue";
import TicketWrapper from "@/Components/Profile/TicketWrapper.vue";
import StickerWrapper from "@/Components/Profile/StickerWrapper.vue";
import type Session from "@/Types/Session";
import type { User } from "@/Types/User";
import EnrolledParticipants from "@/Components/Profile/EnrolledParticipants.vue";
import type Participant from "@/Types/Participant";
import type { Tabs } from "@/Types/ProfilePage";

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

const isParticipant = computed(
    () => user.usertype_type === "App\\Models\\Participant",
);
const isCompany = computed(() => user.usertype_type === "App\\Models\\Company");

const buttons: Tabs = isParticipant.value
    ? {
          ticket: {
              label: "Bilhetes",
              component: h(TicketWrapper),
          },
          sticker: {
              label: "Conquistas",
              component: h(StickerWrapper),
          },
      }
    : isCompany.value
    ? {
          visitHistory: {
              label: "Visitas",
              component: h(EnrolledParticipants),
          },
      }
    : {};
</script>

<template>
    <AppLayout title="Profile">
        <div class="flex flex-col items-center bg-2023-bg pt-6 sm:pt-0">
            <div
                class="relative m-6 min-h-screen w-full flex flex-col items-center p-6 md:max-w-[85vw]"
            >
                <div class="flex w-full justify-around max-md:flex-col">
                    <ProfilePicture :item="user" />
                    <InfoCard :item="user" />
                </div>
                <CvArea v-if="canViewCV" :item="user" />
                <InteractionArea :buttons="buttons"> </InteractionArea>
            </div>
        </div>
    </AppLayout>
</template>
