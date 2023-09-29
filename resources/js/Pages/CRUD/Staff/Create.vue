<script setup lang="ts">
import type Participant from "@/Types/Participant";
import type Edition from "@/Types/Edition";
import type Department from "@/Types/Department";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import { computed } from "vue";
import TextInput from "@/Components/TextInput.vue";

interface Props {
    with: {
        participants: Participant[];
        editions: Edition[];
        departments: Department[];
    };
}

const props = defineProps<Props>();

const form = useForm({
    department_id: "",
    participant_id: "",
    coordinator: false,
});

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);

const submit = () => {
    form.post(route("admin.staff.store"));
};
</script>

<template>
    <CardLayout title="Adicionar Staff">
        <form class="contents" @submit.prevent="submit">
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
                    {{ department.name }}
                    - {{ editions[department.edition_id] }}
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
                    {{ participant.user?.name }} - {{ participant.user?.id }}
                </option>
            </TextInput>

            <label for="coordinator" class="flex flex-row items-center gap-6">
                Coordenador
                <Checkbox
                    id="coordinator"
                    v-model="form.coordinator"
                    :checked="false"
                    value="false"
                />
            </label>

            <PrimaryButton type="submit">Adicionar</PrimaryButton>
        </form>
    </CardLayout>
</template>
