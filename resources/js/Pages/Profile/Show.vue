<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProfileCard from "@/Components/Profile/ProfileCard.vue";
import CvArea from "@/Components/Profile/CvArea.vue";
import InteractionArea from "@/Components/Profile/InteractionArea.vue";
import type Event from "@/Types/Event";
import type Slot from "@/Types/Slot";
import { h, computed } from "vue";
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
    user: User;
    isStaff: boolean;
    canViewCV: boolean;
    canViewAll: boolean;
    points?: number;
}

const props = defineProps<Props>();
const page = usePage();

const authUser = computed(() => page.props.auth.user as User | undefined);

const buttons = computed<Tabs>(() => {
    const list: Tabs = {};

    if (isParticipant(props.user) && !isCompany(authUser.value)) {
        list.ticket = {
            label: "Bilhetes",
            component: h(TicketWrapper),
        };
        list.sticker = {
            label: "Conquistas",
            component: h(StickerWrapper),
        };
    } else if (isCompany(props.user)) {
        list.visitHistory = {
            label: "Visitas",
            component: h(EnrolledParticipants),
        };
    }

    if (
        props.canViewAll &&
        authUser.value &&
        authUser.value.id === props.user.id
    ) {
        list.allParticipants = {
            label: "Todos",
            component: h(AllParticipants),
        };
    }

    return list;
});
</script>

<template>
    <AppLayout title="Perfil">
        <div
            class="relative mx-auto w-full max-w-7xl space-y-8 px-4 py-12 sm:px-6 sm:py-16 lg:px-8"
        >
            <!-- Profile Hero Card -->
            <ProfileCard :user="user" :is-staff="isStaff" :points="points" />

            <!-- CV Upload / Preview Area -->
            <CvArea v-if="canViewCV && isParticipant(user)" :item="user" />

            <!-- Tabs Section (Bilhetes / Conquistas / Visitas) -->
            <InteractionArea :buttons="buttons" />
        </div>
    </AppLayout>
</template>
