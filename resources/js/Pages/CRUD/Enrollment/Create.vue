<script setup lang="ts">
import type Student from "@/Types/Student";
import type Edition from "@/Types/Edition";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    with: {
        students: Student[];
        editions: Edition[];
    };
}

defineProps<Props>();

const form = useForm({
    student_id: "",
    edition_id: "",
    points: 0,
});

const submit = () => {
    form.post(route("admin.enrollments.store"));
};
</script>

<template>
    <CardLayout title="Criar inscrição">
        <form class="contents" @submit.prevent="submit">
            <select v-model="form.student_id" required class="self-stretch">
                <option value="" disabled selected hidden>User</option>
                <option
                    v-for="student in $props.with.students"
                    :key="student.id"
                    :value="student.id"
                >
                    {{ student.user?.name }} - {{ student.user?.id }}
                </option>
            </select>

            <select v-model="form.edition_id" required class="self-stretch">
                <option value="" disabled selected hidden>Edição</option>
                <option
                    v-for="edition in $props.with.editions"
                    :key="edition.id"
                    :value="edition.id"
                >
                    {{ edition.name }}
                </option>
            </select>

            <PrimaryButton type="submit">Adicionar</PrimaryButton>
        </form>
    </CardLayout>
</template>
