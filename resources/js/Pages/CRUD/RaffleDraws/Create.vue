<script setup lang="ts">
import { computed, watch } from "vue";
import TextInput from "@/Components/Form/TextInput.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface EventOption {
    id: number;
    name: string;
    event_day_id?: number;
    event_day?: {
        id: number;
        edition_id: number;
    };
}

interface Props {
    with: {
        editions: Edition[];
        events?: EventOption[];
    };
}

const props = defineProps<Props>();

const form = useForm({
    edition_id: props.with.editions?.[0]?.id?.toString() ?? "",
    required_event_id: "",
    name: "",
    description: "",
    ticket_price: 10,
    is_active: true,
    draw_at: "",
});

// Strictly filter events to ONLY those belonging to the selected edition
const availableEvents = computed(() => {
    if (!props.with.events || !form.edition_id) return [];
    return props.with.events.filter((e) => {
        const editionId = e.event_day?.edition_id;
        return (
            editionId !== undefined &&
            editionId !== null &&
            editionId.toString() === form.edition_id.toString()
        );
    });
});

// Reset required_event_id if the selected edition changes and the event does not belong to it
watch(
    () => form.edition_id,
    (newEditionId) => {
        if (!newEditionId) {
            form.required_event_id = "";
            return;
        }
        const currentEvent = props.with.events?.find(
            (e) => e.id.toString() === form.required_event_id,
        );
        if (
            currentEvent &&
            currentEvent.event_day?.edition_id?.toString() !==
                newEditionId.toString()
        ) {
            form.required_event_id = "";
        }
    },
);

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
            id="required_event_id"
            v-model="form.required_event_id"
            type="select"
            label="Presença Obrigatória (Palestra/Workshop Opcional)"
            :disabled="!form.edition_id"
            :error-message="form.errors.required_event_id"
        >
            <option value="">
                {{ form.edition_id ? "Nenhum (aberto a todos os inscritos com pontos)" : "Seleciona primeiro uma edição" }}
            </option>
            <option
                v-for="event in availableEvents"
                :key="event.id"
                :value="event.id"
            >
                {{ event.name }}
            </option>
        </TextInput>

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
