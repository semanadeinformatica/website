<script setup lang="ts">
import { ref, computed } from "vue";
import type Department from "@/Types/Department";
import AppLayout from "@/Layouts/AppLayout.vue";
import DepartmentSection from "@/Components/Team/DepartmentSection.vue";
import DepartmentSidebar from "@/Components/Team/DepartmentSidebar.vue";
import ComingSoon from "@/Components/UI/ComingSoon.vue";
import QuickScroll from "@/Components/UI/QuickScroll.vue";

interface Props {
    departments: Department[];
}

const props = defineProps<Props>();

const selectedDepartmentId = ref<number | null>(null);

const departmentsWithStaff = computed(() =>
    props.departments.filter((d) => (d.staff?.length ?? 0) > 0),
);

const hasStaff = computed(() => departmentsWithStaff.value.length > 0);

const displayedDepartments = computed(() => {
    if (selectedDepartmentId.value === null) {
        return departmentsWithStaff.value;
    }
    return departmentsWithStaff.value.filter(
        (d) => d.id === selectedDepartmentId.value,
    );
});
</script>

<template>
    <AppLayout title="Equipa">
        <QuickScroll mode="top" />

        <div
            class="relative mx-auto w-full max-w-7xl px-4 pt-0 pb-12 sm:px-6 sm:pb-16 lg:px-8 lg:pt-8"
        >
            <template v-if="hasStaff">
                <div class="lg:flex lg:items-start lg:gap-8 xl:gap-12">
                    <aside
                        class="w-full lg:sticky lg:top-24 lg:w-64 lg:shrink-0 xl:w-72"
                    >
                        <DepartmentSidebar
                            :departments="departmentsWithStaff"
                            :selected-id="selectedDepartmentId"
                            @select="selectedDepartmentId = $event"
                        />
                    </aside>

                    <div class="min-w-0 flex-1">
                        <div class="space-y-12 sm:space-y-16">
                            <DepartmentSection
                                v-for="department in displayedDepartments"
                                :key="department.id"
                                :department="department"
                                :show-header="selectedDepartmentId === null"
                            />
                        </div>
                    </div>
                </div>
            </template>

            <ComingSoon v-else class="py-28" />
        </div>
    </AppLayout>
</template>
