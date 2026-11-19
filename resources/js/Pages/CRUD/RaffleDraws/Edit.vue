<script setup lang="ts">
import TextInput from "@/Components/Form/TextInput.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Edition from "@/Types/Edition";
import type RaffleDraw from "@/Types/Raffle";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    item: RaffleDraw;
    with: {
        editions: Edition[];
    };
}

const props = defineProps<Props>();
const raffleDraw = props.item;

const form = useForm({
    _method: "PUT",
    edition_id: raffleDraw.edition_id.toString(),
    place: raffleDraw.place?.toString() ?? "1",
    name: raffleDraw.name,
    description: raffleDraw.description ?? "",
    is_active: raffleDraw.is_active,
    draw_at: raffleDraw.draw_at ? raffleDraw.draw_at.substring(0, 16) : "",
});

const submit = () => {
    form.post(route("admin.raffleDraws.update", { raffleDraw: raffleDraw.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar sorteio"
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
                disabled
                label="Edição"
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
        </div>

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
            Só entram participantes com scan na Sessão de Encerramento. Staff e
            administração ficam sempre de fora do sorteio. A imagem apresentada
            vem do Prémio Final com a mesma posição.
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

        <div
            v-if="raffleDraw.winner_enrollment_id"
            class="rounded-xl border border-white/10 bg-white/5 p-3 text-xs text-neutral-300"
        >
            <span class="text-neutral-400">Vencedor: </span>
            <span class="font-medium text-amber-400">
                {{
                    raffleDraw.winner?.participant?.user?.name ??
                    `#${raffleDraw.winner_enrollment_id}`
                }}
            </span>
        </div>
    </CRUDModal>
</template>
