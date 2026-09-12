<script setup lang="ts">
import Header from "./Header.vue";
</script>

<template>
    <tr class="border-b border-white/10 bg-white/[0.03]">
        <Header sort-by="id" class="w-16">ID</Header>
        <slot></slot>
        <Header class="w-32 text-right">Ações</Header>
    </tr>
</template>
