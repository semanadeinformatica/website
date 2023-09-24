<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import useSearch from "@/composables/useSearch";
import TextInput from "@/Components/TextInput.vue";
import type Competition from "@/Types/Competition";

interface Props {
    competitions: Competition[];
};

const query = useSearch();

</script>

<template>
    <AppLayout title="Competitions">
        <header class="w-full flex justify-between">
            <div>Competições deste ano</div>
            <TextInput v-model="query" label="Pesquisar" type="search" />
        </header>
        <div></div>
    </AppLayout>
</template>
