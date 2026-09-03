<script setup lang="ts">
import NavLink from "@/Components/NavLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { route } from "ziggy-js";
import { User } from "@lucide/vue";

interface Route {
    label: string;
}
type Routes = Record<string, Route>;

const pageRoutes: Routes = {
    "admin.index": {
        label: "SINF",
    },
};
</script>

<template>
    <nav class="bg-2023-bg relative z-30 flex border-b-2 border-black py-6">
        <div class="flex h-full w-full pl-5 md:hidden">
            <img
                class="flex md:hidden"
                src="/images/cy-sinf-small.svg"
                alt="Stylized SINF logo"
            />
        </div>
        <div class="ml-4 hidden w-full min-w-fit md:flex lg:gap-4">
            <template v-for="({ label }, page) in pageRoutes" :key="page">
                <NavLink
                    :href="route(page)"
                    :active="page === route().current()"
                >
                    {{ label }}
                </NavLink>
            </template>
        </div>
        <div class="ml-2 lg:mx-4">
            <template v-if="$page.props.auth.user">
                <Dropdown align="right" width="32">
                    <template #trigger>
                        <img
                            class="h-10 w-10 cursor-pointer rounded-full object-cover"
                            :src="$page.props.auth.user.profile_photo_url"
                            :alt="$page.props.auth.user.name"
                        />
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.show')">
                            Perfil
                        </DropdownLink>
                        <DropdownLink :href="route('home')">
                            Voltar ao site
                        </DropdownLink>
                        <DropdownLink :href="route('logout')" method="post">
                            Logout
                        </DropdownLink>
                    </template>
                </Dropdown>
            </template>
            <template v-else>
                <a
                    :href="route('login')"
                    class="flex items-center p-1 text-neutral-300 hover:text-white"
                >
                    <User :size="24" />
                </a>
            </template>
        </div>
    </nav>
</template>
