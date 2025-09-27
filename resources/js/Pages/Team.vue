<script setup lang="ts">
import type Department from "@/Types/Department";
import AppLayout from "@/Layouts/AppLayout.vue";
import SINFTeam from "@/Components/Team/SINFTeam.vue";

interface Props {
    departments: Department[];
}

defineProps<Props>();

const colors: Array<string> = [
    "2025-bg",
];

</script>

<template>
    <AppLayout title="Equipa">
        <div
            v-if="departments.length > 0"
            class="flex flex-col items-center gap-7 pt-10"
        >
            <h1
                class="w-fit border border-white rounded-md bg-2025-bg p-2 px-5 text-2xl font-bold text-white shadow-md shadow-black/80"
            >
                Equipa
            </h1>
            <SINFTeam
                v-for="department in departments"
                :key="department.id"
                :color="colors[department.id % colors.length]"
                :department="department"
            ></SINFTeam>
        </div>
        <div v-else>
            <p class="pt-80 text-center text-5xl font-bold text-2023-teal-dark">
                Em breve...
            </p>
        </div>
    </AppLayout>
</template>

<style scoped>
main {
    padding: 2rem;
    padding-inline: 4rem;
}
</style>
