<script setup lang="ts">
import type { User } from "@/Types/User";
import type Paginated from "@/Types/Paginated";
import CRUDLayout from "@/Layouts/CRUDLayout.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import route from "ziggy-js";
import { Link } from "@inertiajs/vue3";

interface Props {
    items: Paginated<User>;
    isSearchable?: boolean;
}

defineProps<Props>();

const usertypeMap = {
    "App\\Models\\Admin": "Administrador",
    "App\\Models\\Participant": "Participante",
    "App\\Models\\Company": "Empresa",
    "App\\Models\\Speaker": "Orador",
} as const satisfies Record<User["usertype_type"], string>;
</script>

<template>
    <CRUDLayout
        title="User"
        :items="items"
        name="users"
        :is-searchable="isSearchable"
    >
        <template #heading>Utilizadores</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="email">Email</Header>
                <Header filter-by="usertype_type" :filter-values="usertypeMap"
                    >Tipo</Header
                >
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="users" :item="item">
                <Cell
                    ><Link :href="route('user.profile', item)">{{
                        item.name
                    }}</Link></Cell
                >
                <Cell>{{ item.email }}</Cell>
                <Cell>{{ usertypeMap[item.usertype_type] }}</Cell>
            </Row>
        </template>
    </CRUDLayout>
</template>
