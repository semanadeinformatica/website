<script setup lang="ts">
import type { User } from "@/Types/User";
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";

interface Props {
    items: Paginated<User>;
}

defineProps<Props>();
</script>

<template>
    <CRUDLayout title="User" :items="items" name="users">
        <template #heading>Utilizadores</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="email">Email</Header>
                <Header>Tipo</Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="users" :item="item">
                <Cell>{{ item.name }}</Cell>
                <Cell>{{ item.email }}</Cell>
                <Cell>{{ item.usertype_type.split("\\").pop()! }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
