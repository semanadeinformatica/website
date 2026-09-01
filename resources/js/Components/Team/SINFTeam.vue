<script setup lang="ts">
import type Department from "@/Types/Department";
import DepartmentSection from "./DepartmentSection.vue";

interface Props {
    department: Department;
    color?: string;
}

defineProps<Props>();
</script>

<template>
    <DepartmentSection :department="department" />
</template>
