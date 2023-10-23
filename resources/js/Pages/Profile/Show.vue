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
import { type User, isCompany, isParticipant } from "@/Types/User";
import EnrolledParticipants from "@/Components/Profile/EnrolledParticipants.vue";
import type { Tabs } from "@/Types/ProfilePage";
import { usePage } from "@inertiajs/vue3";

interface Props {
    confirmsTwoFactorAuthentication: boolean;
    tickets: Event[];
    slots: Slot[];
    sessions: Session[];
    user: User; // The user to render, can be the logged in user or another user
    isStaff: boolean;
    canViewCV: boolean;
    points?: number;
}

const { user } = defineProps<Props>();
const page = usePage();

const buttons: Tabs =
    isParticipant(user) && !isCompany(page.props.auth.user!)
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
        : isCompany(user)
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
                class="relative m-6 flex min-h-screen w-full flex-col items-center p-6 md:max-w-[85vw]"
            >
                <div class="flex w-full justify-around max-md:flex-col">
                    <ProfilePicture :item="user" />
                    <InfoCard :user="user" :is-staff="isStaff" />
                </div>
                <CvArea v-if="canViewCV" :item="user" />
                <p
                    v-if="points !== null"
                    class="text-center text-xl font-bold text-2023-teal-dark"
                >
                    Tens {{ points }}
                    <img
                        class="inline w-5 align-text-top"
                        alt="SINFrão"
                        title="SINFrão"
                        src="/images/cy-sinf-small.svg"
                    />
                </p>
                <InteractionArea :buttons="buttons"> </InteractionArea>
            </div>
        </div>
    </AppLayout>
</template>
