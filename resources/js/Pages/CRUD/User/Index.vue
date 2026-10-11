<script setup lang="ts">
import type { User } from "@/Types/User";
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import { route } from "ziggy-js";
import { Link } from "@inertiajs/vue3";

interface Props {
    items: Paginated<User>;
    isSearchable?: boolean;
}

defineProps<Props>();

const usertypeMap: Record<string, string> = {
    "App\\Models\\Admin": "Administrador",
    "App\\Models\\Participant": "Participante",
    "App\\Models\\Company": "Empresa",
    "App\\Models\\Speaker": "Orador",
};
</script>

<template>
    <CRUDView
        title="Utilizadores"
        view="User"
        :items="items"
        name="users"
        :is-searchable="isSearchable"
    >
        <template #heading>Utilizadores</template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="email">Email</Header>
                <Header filter-by="usertype_type" :filter-values="usertypeMap">
                    Tipo de Perfil
                </Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row name="users" :item="item">
                <Cell class="font-medium">
                    <Link
                        :href="route('user.profile', { user: item })"
                        class="text-white transition-colors hover:text-neutral-300 hover:underline"
                    >
                        {{ item.name }}
                    </Link>
                </Cell>
                <Cell class="font-mono text-xs text-neutral-400">
                    {{ item.email }}
                </Cell>
                <Cell class="text-xs text-neutral-300">
                    {{ usertypeMap[item.usertype_type] || "Utilizador" }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
