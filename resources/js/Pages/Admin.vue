<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed } from "vue";
import route from "ziggy-js";

interface Route {
    label: string;
}
type Routes = Record<string, Route>;

const pageRoutes: Routes = {
    "admin.editions.index": {
        label: "Editions",
    },
    "admin.events.index": { label: "Events" },
    "admin.products.index": { label: "Products" },
    "admin.quests.index": { label: "Quests" },
    "admin.speakers.index": { label: "Speakers" },
    "admin.users.index": {
        label: "Users",
    },
    "admin.sponsors.index": {
        label: "Sponsors",
    },
    "admin.staff.index": {
        label: "Staff",
    },
    "admin.departments.index": {
        label: "Departments",
    },
    "admin.stands.index": {
        label: "Stands",
    },
    "admin.eventDays.index": {
        label: "Event Days",
    },
};

const numCols = computed(() => {
    const numRoutes = Object.keys(pageRoutes).length;
    return `grid-template-columns: repeat(${Math.ceil(
        numRoutes / 2,
    )}, 1fr); grid-template-rows: repeat(2, 1fr);`;
});
</script>

<template>
    <AdminLayout title="Admin">
        <div class="grid items-center gap-10 p-10" :style="numCols">
            <template v-for="({ label }, page) in pageRoutes" :key="page">
                <a
                    class="w-full place-self-center border border-black bg-2023-teal-dark p-6 text-center text-white shadow-2023-red transition-all hover:shadow-md"
                    :href="route(page)"
                    >{{ label }}</a
                >
            </template>
        </div>
    </AdminLayout>
</template>

<style></style>
