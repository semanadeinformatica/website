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
import AllParticipants from "@/Components/Profile/AllParticipants.vue";

interface Props {
    confirmsTwoFactorAuthentication: boolean;
    tickets: Event[];
    slots: Slot[];
    sessions: Session[];
    user: User; // The user to render, can be the logged in user or another user
    isStaff: boolean;
    canViewCV: boolean;
    canViewAll: boolean;
    points?: number;
}

const { user, canViewAll } = defineProps<Props>();
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

if (canViewAll) {
    buttons["allParticipants"] = {
        label: "Todos",
        component: h(AllParticipants),
    };
}

const authUser = page.props.auth.user;
</script>

<template>
    <AppLayout title="Perfil">
        <div class="flex flex-col items-center sm:pt-0">
            <div
                class="relative mx-6 flex min-h-screen w-full flex-col items-center p-6 md:max-w-[60vw]"
            >
                <div class="flex w-full justify-between max-md:flex-col max-md:space-y-8">
                    <ProfilePicture :item="user" />
                    <InfoCard :user="user" :is-staff="isStaff" />
                </div>
                <CvArea v-if="canViewCV && isParticipant(user)" :item="user" />
                <p
                    v-if="points !== null && authUser && authUser.id === user.id"
                    class="text-center text-xl font-bold text-text-color"
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
