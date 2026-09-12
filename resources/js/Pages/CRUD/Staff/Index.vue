<script setup lang="ts">
import type Paginated from "@/Types/Paginated";
import type Participant from "@/Types/Participant";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import type Staff from "@/Types/Staff";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
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
    isSearchable?: boolean;
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
            participant.user?.name ?? `Membro #${participant.id}`,
        ]),
    ),
);
</script>

<template>
    <CRUDView
        title="Membros da Equipa"
        view="Staff"
        :items="items"
        name="staff"
        :is-searchable="isSearchable"
    >
        <template #heading>Equipa & Colaboradores</template>

        <template #header>
            <HeaderRow>
                <Header filter-by="participant_id" :filter-values="participants">
                    Membro
                </Header>
                <Header filter-by="department_id" :filter-values="departments">
                    Departamento
                </Header>
                <Header
                    filter-by="coordinator"
                    :filter-values="{ true: 'Sim', false: 'Não' }"
                >
                    Coordenador
                </Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="staff" :item="item">
                <Cell class="font-medium text-white">
                    {{ participants[item.participant_id] ?? `Membro #${item.participant_id}` }}
                </Cell>
                <Cell class="text-xs text-neutral-400">
                    {{ departments[item.department_id] ?? "-" }}
                </Cell>
                <Cell>
                    <span
                        v-if="item.coordinator"
                        class="inline-flex items-center text-xs font-medium text-emerald-400"
                    >
                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-400" />
                        Sim
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center text-xs text-neutral-500"
                    >
                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-neutral-600" />
                        Não
                    </span>
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
