<script setup lang="ts">
import type Student from "@/Types/Student";
import type Staff from "@/Types/Staff";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import { computed } from "vue";

interface Props {
    item: Staff;
    with: {
        students: Student[];
        editions: Edition[];
        departments: Department[];
    };
}

const props = defineProps<Props>();

const form = useForm({
    department_id: props.item.department_id,
    student_id: props.item.student_id,
    coordinator: props.item.coordinator,
});

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);

const submit = () => {
    form.put(route("admin.staff.update", props.item));
};
</script>

<template>
    <CardLayout title="Editar Staff">
        <form class="contents" @submit.prevent="submit">
            <select v-model="form.department_id" required class="self-stretch">
                <option value="" disabled selected hidden>
                    Departamento - Edição
                </option>
                <option
                    v-for="department in $props.with.departments"
                    :key="department.id"
                    :value="department.id"
                >
                    {{
                        `${department.name} - ${
                            editions[department.edition_id]
                        }`
                    }}
                </option>
            </select>

            <select v-model="form.student_id" required class="self-stretch">
                <option value="" disabled selected hidden>User</option>
                <option
                    v-for="student in $props.with.students"
                    :key="student.id"
                    :value="student.id"
                >
                    {{ `${student.user?.name} - ${student.user?.id}` }}
                </option>
            </select>

            <label for="coordinator" class="flex flex-row items-center gap-6">
                Coordenador
                <Checkbox
                    id="coordinator"
                    v-model="form.coordinator"
                    :checked="false"
                    :value="false"
                />
            </label>

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
