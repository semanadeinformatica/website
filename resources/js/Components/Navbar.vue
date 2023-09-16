<script setup lang="ts">
import NavLink from "@/Components/NavLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownTrigger from "@/Components/DropdownTrigger.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import HamburgerMenu from "@/Components/HamburgerMenu.vue";
import route from "ziggy-js";

const pageRoutes = {
    aboutus: "Sobre nós",
    speakers: "Speakers",
    program: "Programa",
    team: "Equipa",
    sponsors: "Patrocínios",
    contacts: "Contacts",
};

const activityRoutes = {
    talks: "Palestras",
    workshops: "Workshops",
    ctf: "CTF",
    competition: "Competition",
    groupdynamics: "Dinâmicas de Grupo",
};

const editionRoutes = [2022, 2021, 2020, 2019, 2018];

const options = {
    pages: pageRoutes,
    activities: activityRoutes,
    editions: editionRoutes,
};

console.log(route().current());
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
            <template v-for="(label, page) in pageRoutes" :key="page">
                <NavLink
                    :href="route(route().has(page) ? page : 'home')"
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
                            v-for="(label, page) in activityRoutes"
                            :key="page"
                        >
                            <DropdownLink
                                :href="route(route().has(page) ? page : 'home')"
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
                            <DropdownLink :href="route('dashboard')">
                                {{ edition }}
                            </DropdownLink>
                        </template>
                    </template>
                </Dropdown>
            </div>

            <div class="ml-2 lg:mx-4">
                <template v-if="$page.props.auth.user">
                    <Dropdown align="right" width="20">
                        <template #trigger>
                            <img
                                class="h-10 w-10 cursor-pointer rounded-full object-cover"
                                :src="$page.props.auth.user.profile_photo_url"
                                :alt="$page.props.auth.user.name"
                            />
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.show')">
                                Profile
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
