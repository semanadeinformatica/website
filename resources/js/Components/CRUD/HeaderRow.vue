<script setup lang="ts">
import Header from "./Header.vue";
</script>

<template>
    <tr class="bg-2023-teal-dark border border-black text-white">
        <Header sort-by="id">ID</Header>
        <slot></slot>
        <Header>Ações</Header>
    </tr>
</template>
