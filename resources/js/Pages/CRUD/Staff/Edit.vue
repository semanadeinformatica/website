<script setup lang="ts">
import type Participant from "@/Types/Participant";
import type Staff from "@/Types/Staff";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed } from "vue";
import TextInput from "@/Components/Form/TextInput.vue";

interface Props {
    item: Staff;
    with: {
        participants: Participant[];
        editions: Edition[];
        departments: Department[];
    };
}

const props = defineProps<Props>();

const form = useForm({
    department_id: props.item.department_id.toString(),
    participant_id: props.item.participant_id.toString(),
    coordinator: props.item.coordinator,
});

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);

const submit = () => {
    form.put(route("admin.staff.update", { staff: props.item.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar Membro de Equipa"
        name="staff"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
        <TextInput
            v-model="form.department_id"
            type="select"
            required
            label="Departamento"
            :error-message="form.errors.department_id"
        >
            <option
                v-for="department in $props.with.departments"
                :key="department.id"
                :value="department.id"
            >
                {{ department.name }} - {{ editions[department.edition_id] }}
            </option>
        </TextInput>

        <TextInput
            v-model="form.participant_id"
            type="select"
            required
            label="Participante"
            :error-message="form.errors.participant_id"
        >
            <option
                v-for="participant in $props.with.participants"
                :key="participant.id"
                :value="participant.id"
            >
                {{ participant.user?.name ?? `Membro #${participant.id}` }}
            </option>
        </TextInput>

        <div
            class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3"
        >
            <span class="text-xs font-medium text-neutral-300">
                Coordenador do Departamento?
            </span>
            <Checkbox id="coordinator" v-model:checked="form.coordinator" />
        </div>
    </CRUDModal>
</template>
