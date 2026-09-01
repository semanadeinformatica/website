<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import type Department from "@/Types/Department";
import AppLayout from "@/Layouts/AppLayout.vue";
import DepartmentSection from "@/Components/Team/DepartmentSection.vue";
import DepartmentSelector from "@/Components/Team/DepartmentSelector.vue";

interface Props {
    departments: Department[];
}

const props = defineProps<Props>();

const selectedDepartmentId = ref<number | null>(null);
const isAtBottom = ref(false);

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

function updateScrollState() {
    const scrollY = window.scrollY || window.pageYOffset;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    isAtBottom.value = scrollY + windowHeight >= documentHeight - 60;
}

function handleQuickScroll() {
    if (isAtBottom.value) {
        window.scrollTo({ top: 0, behavior: "smooth" });
        return;
    }

    const sections = Array.from(
        document.querySelectorAll<HTMLElement>("section"),
    );
    if (!sections.length) return;

    const currentY = window.scrollY || window.pageYOffset;
    const navOffset = 70;

    const nextSection = sections.find((section) => {
        const top = section.getBoundingClientRect().top + currentY;
        return top > currentY + navOffset + 20;
    });

    if (nextSection) {
        const targetY =
            nextSection.getBoundingClientRect().top + currentY - navOffset;
        window.scrollTo({ top: targetY, behavior: "smooth" });
    } else {
        window.scrollTo({
            top: document.documentElement.scrollHeight,
            behavior: "smooth",
        });
    }
}

onMounted(() => {
    updateScrollState();
    window.addEventListener("scroll", updateScrollState, { passive: true });
    window.addEventListener("resize", updateScrollState, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener("scroll", updateScrollState);
    window.removeEventListener("resize", updateScrollState);
});
</script>

<template>
    <AppLayout title="Equipa">
        <button
            v-if="hasStaff && displayedDepartments.length > 1"
            type="button"
            :aria-label="
                isAtBottom ? 'Scroll to top' : 'Scroll to next department'
            "
            class="pill-container fixed right-6 bottom-6 z-40 h-11 w-11 justify-center text-neutral-300 shadow-none transition-all duration-200 hover:scale-110 hover:border-white/25 hover:text-white focus:outline-none active:scale-95 sm:right-8 sm:bottom-8 sm:h-12 sm:w-12"
            @click="handleQuickScroll"
        >
            <svg
                v-if="isAtBottom"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 10l7-7m0 0l7 7m-7-7v18"
                />
            </svg>
            <svg
                v-else
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 14l-7 7m0 0l-7-7m7 7V3"
                />
            </svg>
        </button>

        <div class="relative mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <template v-if="hasStaff">
                <DepartmentSelector
                    :departments="departments"
                    :selected-id="selectedDepartmentId"
                    @select="selectedDepartmentId = $event"
                />

                <div class="space-y-16 sm:space-y-20">
                    <DepartmentSection
                        v-for="department in displayedDepartments"
                        :key="department.id"
                        :department="department"
                    />
                </div>
            </template>

            <div
                v-else
                class="flex flex-col items-center justify-center py-28 text-center"
            >
                <div class="pill-container mb-4 px-6 py-2.5">
                    <span class="text-sm font-medium text-neutral-400">
                        Em breve...
                    </span>
                </div>
                <p class="max-w-md text-sm text-neutral-400">
                    A equipa da edição 2026 estará disponível brevemente.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
