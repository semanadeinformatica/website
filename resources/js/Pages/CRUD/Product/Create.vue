<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Edition from "@/Types/Edition";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

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
});

const submit = () => {
    form.post(route("admin.products.store"));
};
</script>

<template>
    <CardLayout title="Criar produto">
        <form class="contents" @submit.prevent="submit">
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

            <select
                v-model="form.edition_id"
                required
                class="self-stretch"
            >
                <option value="" disabled selected hidden>
                    Edição
                </option>
                <option
                    v-for="edition in $props.with.editions"
                    :key="edition.id"
                    :value="edition.id"
                >
                    {{ edition.name }}
                </option>
            </select>

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
