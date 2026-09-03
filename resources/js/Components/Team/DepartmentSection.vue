<script setup lang="ts">
import type Department from "@/Types/Department";
import TeamMember from "./TeamMember.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";

interface Props {
    department: Department;
}

defineProps<Props>();
</script>

<template>
    <section
        v-if="(department.staff?.length ?? 0) > 0"
        :id="`dept-${department.id}`"
        class="w-full"
    >
        <div class="mb-8 flex items-center justify-center">
            <PillSelector
                :items="[
                    {
                        id: department.id,
                        label: department.name,
                        active: true,
                    },
                ]"
                size="sm"
                :wrap="false"
            />
        </div>

        <div
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 md:grid-cols-3 lg:grid-cols-4"
        >
            <TeamMember
                v-for="staff in department.staff"
                :key="staff.id"
                :staff="staff"
            />
        </div>
    </section>
</template>
