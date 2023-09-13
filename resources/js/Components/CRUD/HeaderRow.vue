<script setup lang="ts">
import Header from "./Header.vue";
</script>

<template>
    <tr class="border border-black bg-2023-teal-dark text-white">
        <Header>ID</Header>
        <slot></slot>
        <Header>Ações</Header>
    </tr>
</template>
