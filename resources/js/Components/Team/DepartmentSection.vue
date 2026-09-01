<script setup lang="ts">
import type Department from "@/Types/Department";
import TeamMember from "./TeamMember.vue";

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
            <div class="pill-container gap-2 px-5 py-2 shadow-none">
                <h3
                    class="text-xs font-semibold tracking-wider text-neutral-200 uppercase sm:text-sm"
                >
                    {{ department.name }}
                </h3>
            </div>
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
