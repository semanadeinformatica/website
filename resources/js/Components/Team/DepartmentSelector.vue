<script setup lang="ts">
import { computed } from "vue";
import type Department from "@/Types/Department";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";

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

const items = computed<PillOption[]>(() => [
    { id: "all", label: "Todos", count: totalMembers.value },
    ...departmentsWithStaff.value.map((dept) => ({
        id: dept.id,
        label: dept.name,
        count: dept.staff?.length,
    })),
]);
</script>

<template>
    <div
        v-if="departmentsWithStaff.length > 1"
        class="mb-10 flex justify-center"
    >
        <PillSelector
            :model-value="selectedId ?? 'all'"
            :items="items"
            size="md"
            container-class="max-w-full"
            @update:model-value="
                (val) => emit('select', val === 'all' ? null : Number(val))
            "
        />
    </div>
</template>
