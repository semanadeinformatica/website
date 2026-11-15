<script setup lang="ts">
import ImageInput from "@/Components/Form/ImageInput.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Edition from "@/Types/Edition";
import type FinalPrize from "@/Types/FinalPrize";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    item: FinalPrize;
    with: {
        editions: Edition[];
    };
}

const { item: finalPrize } = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    edition_id: finalPrize.edition_id.toString(),
    place: finalPrize.place.toString(),
    title: finalPrize.title,
    image: null as File | null,
});

const submit = () => {
    form.post(route("admin.finalPrizes.update", { finalPrize: finalPrize.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar prémio final"
        name="finalPrizes"
        :processing="form.processing"
        max-width="md"
        @submit="submit"
    >
        <ImageInput
            id="image"
            v-model="form.image"
            :initial-preview="finalPrize.final_prize_image_url ?? undefined"
            label="Imagem do prémio"
            class="self-stretch"
            :error-message="form.errors.image"
        />

        <TextInput
            id="title"
            v-model="form.title"
            label="Título"
            type="text"
            required
            autofocus
            autocomplete="title"
            :error-message="form.errors.title"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                v-model="form.place"
                type="select"
                required
                label="Classificação"
                :error-message="form.errors.place"
            >
                <option value="1">1º Lugar</option>
                <option value="2">2º Lugar</option>
                <option value="3">3º Lugar</option>
            </TextInput>

            <TextInput
                v-model="form.edition_id"
                type="select"
                required
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
    </CRUDModal>
</template>
