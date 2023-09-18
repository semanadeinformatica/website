<script setup lang="ts">
import type Department from "@/Types/Department";
import AppLayout from "@/Layouts/AppLayout.vue";
import SINFTeam from "@/Components/Team/SINFTeam.vue";

interface Props {
    departments: Department[];
}

const { departments } = defineProps<Props>();

const colors: Array<string> = [
    "orange",
    "teal-dark",
    "red-dark",
    "red",
    "teal",
];
</script>

<template>
    <AppLayout title="Team">
        <div class="flex flex-col gap-6 border border-black p-10">
            <SINFTeam
                v-for="department in departments"
                :key="department.id"
                :color="colors[department.id % colors.length]"
                :department="department"
            ></SINFTeam>
        </div>
    </AppLayout>
</template>

<style>
main {
    padding: 2rem;
    padding-inline: 4rem;
}
</style>
