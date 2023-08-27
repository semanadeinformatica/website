<script setup lang="ts">
import NavLink from '@/Components/NavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownTrigger from '@/Components/DropdownTrigger.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import HamburgerMenu from '@/Components/HamburgerMenu.vue';
import route from 'ziggy-js';

const page_routes = {
    'Sobre nós': 'about-us',
    'Speakers': 'speakers',
    'Programa': 'program',
    'Equipa': 'team',
    'Sponsors': 'sponsors',
    'Contactos': 'contacts',
    };

const activity_routes = {
    'Palestras': 'talks',
    'CTF': 'ctf',
    'Workshops': 'workshops',
    'Competição': 'competition',
    'Dinâmicas de grupo': 'group-dynamics',
}

</script>

<template>
    <nav class="flex py-6 bg-2023-bg border-b-2 border-black">

        <div class="ml-2 flex md:hidden">
            <HamburgerMenu :options="page_routes"/>
        </div>

        <div class="hidden md:flex w-full ml-4 lg:gap-4 min-w-fit">
            <template v-for="page in Object.keys(page_routes)">
                <NavLink :href="route('dashboard')" :active="page == 'Sponsors'">
                    {{ page }}
                </NavLink>
            </template>
        </div>

        <div class="flex w-full justify-end items-center gap-1 lg:gap-4 mr-4">

            <Dropdown align="center" width="32">
                <template #trigger>
                    <DropdownTrigger>
                        Atividades v
                    </DropdownTrigger>
                </template>
                <template #content>
                    <template v-for="activity in Object.keys(activity_routes)">
                        <DropdownLink :href="route('dashboard')">
                            {{ activity }}
                        </DropdownLink>
                    </template>
                </template>
            </Dropdown>

            <Dropdown align="center"  width="20">
                <template #trigger>
                    <DropdownTrigger>
                        2023 v
                    </DropdownTrigger>
                </template>
                <template #content>
                    <DropdownLink :href="route('dashboard')">
                        2023
                    </DropdownLink>
                </template>
            </Dropdown>

            <div v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth.user" class="lg:ml-4">
                <a :href="route('profile.show')">
                    <img class="h-10 w-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                </a>
            </div>
            
        </div>

    </nav>
</template>