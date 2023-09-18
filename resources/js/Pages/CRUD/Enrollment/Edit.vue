<script setup lang="ts">
import type Student from "@/Types/Student";
import type Edition from "@/Types/Edition";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";
import type Enrollment from "@/Types/Enrollment";

interface Props {
    item: Enrollment;
    with: {
        students: Student[];
        editions: Edition[];
    };
}

const { item: enrollment } = defineProps<Props>();

const form = useForm({
    student_id: enrollment.student_id,
    edition_id: enrollment.edition_id,
    points: enrollment.points.toString(),
});

const submit = () => {
    form.put(route("admin.enrollments.update", enrollment));
};
</script>

<template>
    <CardLayout title="Editar inscrição">
        <form class="contents" @submit.prevent="submit">
            <select v-model="form.student_id" required class="self-stretch">
                <option value="" selected hidden>User</option>
                <option
                    v-for="student in $props.with.students"
                    :key="student.id"
                    :value="student.id"
                >
                    {{ student.user?.name }} - {{ student.user?.id }}
                </option>
            </select>

            <select
                v-model="form.edition_id"
                required
                disabled
                class="self-stretch"
            >
                <option value="" disabled selected hidden>Edição</option>
                <option
                    v-for="edition in $props.with.editions"
                    :key="edition.id"
                    :value="edition.id"
                >
                    {{ edition.name }}
                </option>
            </select>

            <TextInput
                id="points"
                v-model="form.points"
                label="Pontos"
                type="number"
                :error-message="form.errors.points"
                disabled
            />

            <PrimaryButton type="submit">Adicionar</PrimaryButton>
        </form>
    </CardLayout>
</template>
