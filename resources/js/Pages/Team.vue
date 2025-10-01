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
            class="flex flex-col items-center gap-7 py-4"
        >
            <h1
                class="w-fit border border-solid border-white rounded-lg bg-2025-blue p-3 text-center text-2xl font-bold text-white shadow-md shadow-black/80 xl:text-3xl 2xl:text-4xl"
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
            <p class="pt-80 text-center text-5xl font-bold text-white">
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
