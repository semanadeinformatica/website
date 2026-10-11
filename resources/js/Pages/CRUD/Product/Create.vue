<script setup lang="ts">
import ImageInput from "@/Components/Form/ImageInput.vue";
import TextInput from "@/Components/Form/TextInput.vue";
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
    name: "",
    price: "",
    stock: "",
    edition_id: "",
    image: null as File | null,
});

const submit = () => {
    form.post(route("admin.products.store"));
};
</script>

<template>
    <CRUDModal
        title="Criar produto"
        name="products"
        :processing="form.processing"
        max-width="lg"
        @submit="submit"
    >
        <ImageInput
            id="image"
            v-model="form.image"
            label="Imagem do produto"
            class="self-stretch"
            :error-message="form.errors.image"
        />

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

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                id="price"
                v-model="form.price"
                label="Preço (Pontos)"
                type="number"
                :error-message="form.errors.price"
            />

            <TextInput
                id="stock"
                v-model="form.stock"
                label="Stock"
                type="number"
                :error-message="form.errors.stock"
            />
        </div>

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
    </CRUDModal>
</template>
