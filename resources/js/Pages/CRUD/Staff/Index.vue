<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Student from "@/Types/Student";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import type Staff from "@/Types/Staff";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";

interface Props {
    items: Paginated<Staff>;
    with: {
        students: Student[];
        editions: Edition[];
        departments: Department[];
    };
}

const props = defineProps<Props>();

const departments = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.departments.map((department) => [
            department.id,
            department.name ?? "",
        ]),
    ),
);

const students = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.students.map((student) => [
            student.id,
            student.user?.name ?? "",
        ]),
    ),
);
</script>

<template>
    <CRUDLayout title="Staff" :items="items" name="staff">
        <template #heading>Staff</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header>Student ID</Header>
                <Header>Departamento</Header>
                <Header>Coordenador</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="staff" :item="item">
                <Cell>{{ students[item.student_id] }}</Cell>
                <Cell>{{ item.student_id }}</Cell>
                <Cell>{{ departments[item.department_id] }}</Cell>
                <Cell>{{ item.coordinator ? "✓" : "❌" }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
