<script setup lang="ts">
import { computed } from "vue";
import type Department from "@/Types/Department";

interface Props {
    departments: Department[];
    selectedId: number | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: "select", id: number | null): void;
}>();

const departmentsWithStaff = computed(() =>
    props.departments.filter((d) => (d.staff?.length ?? 0) > 0),
);

const totalMembers = computed(() =>
    departmentsWithStaff.value.reduce(
        (sum, d) => sum + (d.staff?.length ?? 0),
        0,
    ),
);
</script>

<template>
    <div
        v-if="departmentsWithStaff.length > 1"
        class="mb-10 flex justify-center"
    >
        <div
            class="pill-container max-w-full flex-wrap justify-center gap-1.5 p-1.5"
        >
            <button
                type="button"
                class="pill-item cursor-pointer gap-1.5 px-4 py-2 text-xs font-medium transition-all duration-200 sm:text-sm"
                :class="{ 'pill-item-active': selectedId === null }"
                @click="emit('select', null)"
            >
                <span>Todos</span>
                <span
                    class="py-0.2 rounded-full px-1.5 text-[10px] transition-colors"
                    :class="
                        selectedId === null
                            ? 'bg-white/20 text-white'
                            : 'bg-white/8 text-neutral-400'
                    "
                >
                    {{ totalMembers }}
                </span>
            </button>

            <button
                v-for="dept in departmentsWithStaff"
                :key="dept.id"
                type="button"
                class="pill-item cursor-pointer gap-1.5 px-4 py-2 text-xs font-medium transition-all duration-200 sm:text-sm"
                :class="{ 'pill-item-active': selectedId === dept.id }"
                @click="emit('select', dept.id)"
            >
                <span>{{ dept.name }}</span>
                <span
                    v-if="dept.staff?.length"
                    class="py-0.2 rounded-full px-1.5 text-[10px] transition-colors"
                    :class="
                        selectedId === dept.id
                            ? 'bg-white/20 text-white'
                            : 'bg-white/8 text-neutral-400'
                    "
                >
                    {{ dept.staff.length }}
                </span>
            </button>
        </div>
    </div>
</template>
