<script setup lang="ts">
import TextInput from "@/Components/Form/TextInput.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    with: {
        editions: Edition[];
    };
}

defineProps<Props>();

const form = useForm({
    edition_id: "",
    name: "",
    description: "",
    ticket_price: 10,
    is_active: true,
    draw_at: "",
});

const submit = () => {
    form.post(route("admin.raffleDraws.store"));
};
</script>

<template>
    <CRUDModal
        title="Criar sorteio"
        name="raffleDraws"
        :processing="form.processing"
        max-width="lg"
        @submit="submit"
    >
        <TextInput
            id="name"
            v-model="form.name"
            label="Nome"
            type="text"
            required
            autofocus
            autocomplete="name"
            :error-message="form.errors.name"
        />

        <TextInput
            id="description"
            v-model="form.description"
            label="Descrição"
            type="textarea"
            :error-message="form.errors.description"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                id="ticket_price"
                v-model="form.ticket_price"
                label="Preço do Bilhete (SINFrões)"
                type="number"
                min="1"
                required
                :error-message="form.errors.ticket_price"
            />

            <TextInput
                v-model="form.edition_id"
                type="select"
                required
                label="Edição"
                :error-message="form.errors.edition_id"
            >
                <option value="" disabled>Selecionar edição</option>
                <option
                    v-for="edition in $props.with.editions"
                    :key="edition.id"
                    :value="edition.id"
                >
                    {{ edition.name }}
                </option>
            </TextInput>
        </div>

        <TextInput
            id="draw_at"
            v-model="form.draw_at"
            label="Data do Sorteio"
            type="datetime-local"
            :error-message="form.errors.draw_at"
        />

        <div class="flex items-center gap-3 pt-1">
            <Checkbox id="is_active" v-model:checked="form.is_active" />
            <label
                for="is_active"
                class="cursor-pointer text-xs font-medium text-neutral-300"
            >
                Sorteio ativo
            </label>
        </div>
    </CRUDModal>
</template>
