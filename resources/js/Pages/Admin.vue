<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { computed } from "vue";
import route from "ziggy-js";

interface Route {
    label: string;
}
type Routes = Record<string, Route>;

const pageRoutes: Routes = {
    "admin.editions.index": {
        label: "Edições",
    },
    "admin.events.index": { label: "Eventos" },
    "admin.products.index": { label: "Produtos" },
    "admin.quests.index": { label: "Quests" },
    "admin.speakers.index": { label: "Speakers" },
    "admin.users.index": {
        label: "Utilizadores",
    },
    "admin.sponsors.index": {
        label: "Patrocinadores",
    },
    "admin.staff.index": {
        label: "Staff",
    },
    "admin.departments.index": {
        label: "Departmentos",
    },
    "admin.stands.index": {
        label: "Bancas",
    },
    "admin.eventDays.index": {
        label: "Dias de Evento",
    },
    "admin.competitions.index": {
        label: "Competições",
    },
    "admin.competitionTeams.index": {
        label: "Equipas nas Competições",
    },
    "admin.slots.index": {
        label: "Slots de tarefas",
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
