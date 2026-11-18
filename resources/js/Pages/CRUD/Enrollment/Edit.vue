<script setup lang="ts">
import type Participant from "@/Types/Participant";
import type Edition from "@/Types/Edition";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type Enrollment from "@/Types/Enrollment";

interface Props {
    item: Enrollment;
    with: {
        participants: Participant[];
        editions: Edition[];
    };
}

const { item: enrollment } = defineProps<Props>();

const form = useForm({
    participant_id: enrollment.participant_id.toString(),
    edition_id: enrollment.edition_id.toString(),
    points: enrollment.points.toString(),
    flagged: enrollment.flagged_at !== null,
    flag_reason: enrollment.flag_reason ?? "",
});

const submit = () => {
    form.put(route("admin.enrollments.update", { enrollment: enrollment.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar inscrição"
        name="enrollments"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
        <TextInput
            id="participant_id"
            v-model="form.participant_id"
            label="Participante"
            type="select"
            required
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

        <TextInput
            id="edition_id"
            v-model="form.edition_id"
            label="Edição"
            type="select"
            required
            :error-message="form.errors.edition_id"
        >
            <option
                v-for="edition in $props.with.editions"
                :key="edition.id"
                :value="edition.id"
            >
                {{ edition.name }}
            </option>
        </TextInput>

        <TextInput
            id="points"
            v-model="form.points"
            label="Pontos"
            type="number"
            :error-message="form.errors.points"
        />

        <div
            class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3"
        >
            <div class="pr-4">
                <span class="text-xs font-medium text-neutral-300">
                    Sinalizar como conta suspeita
                </span>
                <p class="mt-0.5 text-[11px] text-neutral-500">
                    O participante verá um aviso no seu perfil.
                </p>
            </div>
            <Checkbox id="flagged" v-model:checked="form.flagged" />
        </div>

        <TextInput
            v-if="form.flagged"
            id="flag_reason"
            v-model="form.flag_reason"
            label="Motivo (opcional)"
            :error-message="form.errors.flag_reason"
        />
    </CRUDModal>
</template>
