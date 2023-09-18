<script setup lang="ts">
import type Enrollment from "@/Types/Enrollment";
import type Paginated from "@/Types/Paginated";
import type Student from "@/Types/Student";
import type Edition from "@/Types/Edition";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { computed } from "vue";

interface Props {
    items: Paginated<Enrollment>;
    with: {
        students: Student[];
        editions: Edition[];
    };
}

const props = defineProps<Props>();

const students = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.students.map((student) => [
            student.id,
            student.user?.name ?? "",
        ]),
    ),
);

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);
</script>

<template>
    <CRUDLayout title="Enrollment" :items="items" name="enrollments">
        <template #heading>Inscrições</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="student_id">Nome do Estudante</Header>
                <Header sort-by="edition_id">Nome da Edição</Header>
                <Header sort-by="points">Pontos</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="enrollments">
                <Cell>{{ students[item.student_id] }}</Cell>
                <Cell>{{ editions[item.edition_id] }}</Cell>
                <Cell>{{ item.points }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
