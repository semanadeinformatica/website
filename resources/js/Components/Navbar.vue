<script setup lang="ts">
import NavLink from "@/Components/NavLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownTrigger from "@/Components/DropdownTrigger.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import HamburgerMenu from "@/Components/HamburgerMenu.vue";
import route, {
    type QueryParams,
    type RouteParamsWithQueryOverload,
} from "ziggy-js";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

interface Route {
    label: string;
    _query?: QueryParams;
}
type Routes = Record<string, Route>;

const pageRoutes: Routes = {
    aboutus: {
        label: "Sobre nós",
    },
    speakers: { label: "Speakers" },
    program: {
        label: "Programa",
        _query: {
            day: 1,
        },
    },
    team: { label: "Equipa" },
    sponsors: { label: "Patrocínios" },
    contacts: { label: "Contactos" },
    competitions: { label: "Competições" },
};

const activityRoutes: Routes = {
    talks: { label: "Palestras" },
    workshops: { label: "Workshops" },
    ctf: { label: "CTF" },
    competition: { label: "Competition" },
    groupdynamics: { label: "Dinâmicas de Grupo" },
};

const editionRoutes = [2022, 2021, 2020, 2019, 2018];

const options = {
    pages: pageRoutes,
    activities: activityRoutes,
    editions: editionRoutes,
};

const isAdmin = computed(() => {
    return usePage().props.auth.user.usertype_type == "App\\Models\\Admin";
});
</script>

<template>
    <nav class="relative z-30 flex border-b-2 border-black bg-2023-bg py-6">
        <div class="flex h-full w-full pl-5 md:hidden">
            <img
                class="flex md:hidden"
                src="/images/cy-sinf-small.svg"
                alt="Stylized SINF logo"
            />
        </div>
        <div class="ml-4 hidden w-full min-w-fit md:flex lg:gap-4">
            <template
                v-for="({ label, _query }, page) in pageRoutes"
                :key="page"
            >
                <NavLink
                    :href="
                        route(route().has(page) ? page : 'home', {
                            _query,
                        } as RouteParamsWithQueryOverload)
                    "
                    :active="page === route().current()"
                >
                    {{ label }}
                </NavLink>
            </template>
        </div>

        <div class="mr-4 flex w-full items-center justify-end">
            <div class="hidden gap-1 md:flex lg:gap-4">
                <Dropdown align="center" width="32">
                    <template #trigger>
                        <DropdownTrigger> Atividades </DropdownTrigger>
                    </template>
                    <template #content>
                        <template
                            v-for="({ label, _query }, page) in activityRoutes"
                            :key="page"
                        >
                            <DropdownLink
                                :href="
                                    route(route().has(page) ? page : 'home', {
                                        _query,
                                    } as RouteParamsWithQueryOverload)
                                "
                            >
                                {{ label }}
                            </DropdownLink>
                        </template>
                    </template>
                </Dropdown>

                <Dropdown align="center" width="20">
                    <template #trigger>
                        <DropdownTrigger> 2023 </DropdownTrigger>
                    </template>
                    <template #content>
                        <template
                            v-for="edition in editionRoutes"
                            :key="edition"
                        >
                            <DropdownLink :href="route('home')">
                                {{ edition }}
                            </DropdownLink>
                        </template>
                    </template>
                </Dropdown>
            </div>

            <div class="ml-2 lg:mx-4">
                <template v-if="$page.props.auth.user">
                    <Dropdown align="right" :width="isAdmin ? '32' : '20'">
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
                            <DropdownLink
                                v-if="isAdmin"
                                :href="route('admin.index')"
                            >
                                Administração
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post">
                                Logout
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </template>
                <template v-else>
                    <a :href="route('login')">
                        <v-icon name="io-person" scale="1.7" fill="#025259">
                        </v-icon>
                    </a>
                </template>
            </div>

            <HamburgerMenu :options="options" />
        </div>
    </nav>
</template>
