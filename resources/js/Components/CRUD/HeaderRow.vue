<script setup lang="ts">
import Header from "./Header.vue";
</script>

<template>
    <tr class="border border-black bg-2023-teal-dark text-white">
        <Header sort-by="id">ID</Header>
        <slot></slot>
        <Header>Ações</Header>
    </tr>
</template>
