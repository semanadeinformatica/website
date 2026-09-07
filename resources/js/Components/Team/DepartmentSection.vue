<script setup lang="ts">
import type Department from "@/Types/Department";
import TeamMember from "./TeamMember.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";

interface Props {
    department: Department;
    showHeader?: boolean;
}

withDefaults(defineProps<Props>(), {
    showHeader: true,
});
</script>

<template>
    <section
        v-if="(department.staff?.length ?? 0) > 0"
        :id="`dept-${department.id}`"
        class="w-full"
    >
        <div
            v-if="showHeader"
            class="mb-4 flex items-center justify-center sm:justify-start sm:mb-6"
        >
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
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-6 lg:grid-cols-2 xl:grid-cols-3"
        >
            <TeamMember
                v-for="staff in department.staff"
                :key="staff.id"
                :staff="staff"
            />
        </div>
    </section>
</template>
