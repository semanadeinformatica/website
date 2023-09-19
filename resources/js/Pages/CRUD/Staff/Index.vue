<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Participant from "@/Types/Participant";
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
        participants: Participant[];
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

const participants = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.participants.map((participant) => [
            participant.id,
            participant.user?.name ?? "",
        ]),
    ),
);
</script>

<template>
    <CRUDLayout title="Staff" :items="items" name="staff">
        <template #heading>Staff</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="student_id" :filter-values="participants"
                    >Participante</Header
                >
                <Header filter-by="department_id" :filter-values="departments"
                    >Departamento</Header
                >
                <Header
                    filter-by="coordinator"
                    :filter-values="{ true: '✓', false: '❌' }"
                    >Coordenador</Header
                >
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="staff" :item="item">
                <Cell>{{ participants[item.participant_id] }}</Cell>
                <Cell>{{ departments[item.department_id] }}</Cell>
                <Cell>{{ item.coordinator ? "✓" : "❌" }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
