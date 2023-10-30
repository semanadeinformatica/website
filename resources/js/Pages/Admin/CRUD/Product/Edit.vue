<script setup lang="ts">
import ImageInput from "@/Components/ImageInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Edition from "@/Types/Edition";
import type Product from "@/Types/Product";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Product;
    with: {
        editions: Edition[];
    };
}

const { item: product } = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    name: product.name,
    price: product.price.toString(),
    stock: product.stock.toString(),
    edition_id: product.edition_id.toString(),
    image: null as File | null,
});

const submit = () => {
    form.post(route("admin.products.update", product));
};
</script>

<template>
    <CardLayout title="Editar produto">
        <form class="contents" @submit.prevent="submit">
            <ImageInput
                id="image"
                v-model="form.image"
                :initial-preview="item.image_product_url"
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

            <TextInput
                id="price"
                v-model="form.price"
                label="Preço"
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

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
