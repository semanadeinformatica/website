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
            <h2
            class="w-fi bg-2025-blue p-3 text-center text-2xl rounded-md font-bold text-white xl:text-3xl 2xl:text-4xl"
            >
                Equipa
            </h2>
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
