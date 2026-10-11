<script setup lang="ts">
import { ref, computed } from "vue";
import type Department from "@/Types/Department";
import AppLayout from "@/Layouts/AppLayout.vue";
import DepartmentSection from "@/Components/Team/DepartmentSection.vue";
import DepartmentSidebar from "@/Components/Team/DepartmentSidebar.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
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

function handleSelectDepartment(id: number | null) {
    selectedDepartmentId.value = id;
    if (window.scrollY > 200) {
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
}
</script>

<template>
    <AppLayout title="Equipa">
        <QuickScroll
            v-if="hasStaff && displayedDepartments.length > 1"
            mode="sections"
        />

        <div
            class="relative mx-auto w-full max-w-7xl px-4 pt-0 pb-12 sm:px-6 sm:pb-16 lg:px-8 lg:pt-8"
        >
            <template v-if="hasStaff">
                <div class="lg:flex lg:items-start lg:gap-8 xl:gap-12">
                    <aside
                        class="w-full lg:sticky lg:top-24 lg:w-64 lg:shrink-0 xl:w-72"
                    >
                        <DepartmentSidebar
                            :departments="departments"
                            :selected-id="selectedDepartmentId"
                            @select="handleSelectDepartment"
                        />
                    </aside>

                    <!-- Right Column: Team Members -->
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

            <div
                v-else
                class="flex flex-col items-center justify-center py-28 text-center"
            >
                <PillSelector
                    :items="[
                        { id: 'soon', label: 'Em breve...', disabled: true },
                    ]"
                    size="sm"
                    :wrap="false"
                    container-class="mb-4"
                />
                <p class="max-w-md text-sm text-neutral-400">
                    A equipa da edição 2026 estará disponível brevemente.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
