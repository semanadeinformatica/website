<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import route from "ziggy-js";

const pageRoutes = {
    "admin.competitions.index": "models.competition",
    "admin.competitionTeams.index": "models.competitionTeam",
    "admin.departments.index": "models.department",
    "admin.editions.index": "models.edition",
    "admin.eventDays.index": "models.eventDay",
    "admin.events.index": "models.event",
    "admin.products.index": "models.product",
    "admin.quests.index": "models.quest",
    "admin.slots.index": "models.slot",
    "admin.sponsors.index": "models.sponsor",
    "admin.staff.index": "models.staff",
    "admin.stands.index": "models.stand",
    "admin.users.index": "models.user",
    "admin.eventTypes.index": "models.eventType",
    "admin.enrollments.index": "models.enrollment",
    "admin.sponsorTiers.index": "models.sponsorTier",
    "admin.gallery": "admin.gallery"
} satisfies Record<string, string>;
</script>

<template>
    <AdminLayout title="Administrador">
        <div
            class="grid grid-cols-1 items-center gap-10 p-10 sm:grid-cols-[repeat(auto-fill,minmax(theme(spacing.60),1fr))]"
        >
            <template v-for="(label, page) in pageRoutes" :key="page">
                <a
                    class="w-full place-self-center break-words border border-black bg-2023-teal-dark p-6 text-center text-white shadow-2023-red transition-all hover:shadow-md"
                    :href="route(page)"
                    >{{ $t(label, 2) }}</a
                >
            </template>
        </div>
    </AdminLayout>
</template>

<style></style>
