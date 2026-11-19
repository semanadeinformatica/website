<script setup lang="ts">
import { computed, watch } from "vue";
import TextInput from "@/Components/Form/TextInput.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface QuestOption {
    id: number;
    name: string;
    edition_id: number;
}

interface Props {
    with: {
        editions: Edition[];
        quests: QuestOption[];
    };
}

const props = defineProps<Props>();

const form = useForm({
    edition_id: props.with.editions?.[0]?.id?.toString() ?? "",
    place: "1",
    required_quest_id: "",
    name: "",
    description: "",
    is_active: true,
    draw_at: "",
});

const availableQuests = computed(() =>
    props.with.quests.filter(
        (quest) => quest.edition_id.toString() === form.edition_id.toString(),
    ),
);

watch(
    () => form.edition_id,
    () => {
        if (
            form.required_quest_id &&
            !availableQuests.value.some(
                (quest) => quest.id.toString() === form.required_quest_id,
            )
        ) {
            form.required_quest_id = "";
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
                id="place"
                v-model="form.place"
                label="Posição"
                type="select"
                required
                :error-message="form.errors.place"
            >
                <option value="1">1º Prémio</option>
                <option value="2">2º Prémio</option>
                <option value="3">3º Prémio</option>
            </TextInput>

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
            id="required_quest_id"
            v-model="form.required_quest_id"
            label="Tarefa de presença"
            type="select"
            required
            :disabled="!form.edition_id"
            :error-message="form.errors.required_quest_id"
        >
            <option value="" disabled>
                {{
                    form.edition_id
                        ? "Selecionar tarefa"
                        : "Seleciona primeiro uma edição"
                }}
            </option>
            <option
                v-for="quest in availableQuests"
                :key="quest.id"
                :value="quest.id"
            >
                {{ quest.name }}
            </option>
        </TextInput>

        <TextInput
            id="draw_at"
            v-model="form.draw_at"
            label="Data prevista do sorteio"
            type="datetime-local"
            :error-message="form.errors.draw_at"
        />

        <div
            class="rounded-xl border border-amber-400/20 bg-amber-400/5 p-3 text-xs text-amber-200/90"
        >
            Escolhe a tarefa que regista a presença na Sessão de Encerramento.
            Só entram participantes com essa tarefa; staff e administração ficam
            sempre de fora. A imagem apresentada vem do Prémio Final com a mesma
            posição.
        </div>

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
