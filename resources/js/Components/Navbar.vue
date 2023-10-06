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
import { OhVueIcon } from "oh-vue-icons";

interface Route {
    label: string;
    _query?: QueryParams;
}
type Routes = Record<string, Route>;

const homeSections: Routes = {
    aboutus: {
        label: "components.navbar.navLinks.aboutus",
    },
    speakers: { label: "components.navbar.navLinks.speakers" },
    sponsors: { label: "components.navbar.navLinks.sponsors" },
};

const pageRoutes: Routes = {
    program: {
        label: "components.navbar.navLinks.program",
        _query: {
            day: 1,
        },
    },
    shop: { label: "components.navbar.navLinks.shop" },
    team: { label: "components.navbar.navLinks.team" },
};

const editionRoutes = [2022, 2021, 2020, 2019, 2018];

const options = {
    pages: pageRoutes,
    competitions: usePage().props.competitions,
    editions: editionRoutes,
};

const isAdmin = computed(() => {
    return usePage().props.auth.user?.usertype_type == "App\\Models\\Admin";
});
</script>

<template>
    <nav class="sticky top-0 z-30 flex border-b-2 border-black bg-2023-bg py-2">
        <Dropdown align="center" width="32" class="ml-10 max-md:hidden">
            <template #trigger>
                <DropdownTrigger class="group">
                    <a :href="route('home')">
                        <img
                            class="w-24 max-md:w-16"
                            src="/images/cy-sinf-small.svg"
                            alt="Stylized SINF logo"
                        />
                    </a>
                </DropdownTrigger>
            </template>
            <template #content>
                <template v-for="({ label }, page) in homeSections" :key="page">
                    <DropdownLink
                        :href="page !== 'home' ? `/#${page}` : route(page)"
                    >
                        {{ $t(label) }}
                    </DropdownLink>
                </template>
            </template>
        </Dropdown>
        <NavLink :href="route('home')" class="md:hidden">
            <img
                class="w-24 max-md:w-16"
                src="/images/cy-sinf-small.svg"
                :alt="$t('general.sinfLogoAlt')"
            />
        </NavLink>
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
                    {{ $t(label) }}
                </NavLink>
            </template>
            <Dropdown
                v-if="$page.props.competitions.length > 0"
                align="center"
                width="32"
            >
                <template #trigger>
                    <DropdownTrigger>{{
                        $t("components.navbar.competitions")
                    }}</DropdownTrigger>
                </template>
                <template #content>
                    <template
                        v-for="competition in $page.props.competitions"
                        :key="competition.id"
                    >
                        <DropdownLink
                            :href="route('competition.show', { competition })"
                        >
                            {{ competition.name }}
                        </DropdownLink>
                    </template>
                </template>
            </Dropdown>
        </div>

        <div class="mr-4 flex w-full items-center justify-end">
            <div class="hidden gap-1 md:flex lg:gap-4">
                <Dropdown align="center" width="20">
                    <template #trigger>
                        <DropdownTrigger> 2023 </DropdownTrigger>
                    </template>
                    <template #content>
                        <template
                            v-for="edition in editionRoutes"
                            :key="edition"
                        >
                            <DropdownLink
                                as="a"
                                :href="`https://${edition}.sinf.pt`"
                            >
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
                                {{
                                    $t(
                                        "components.navbar.profileDropdown.profile",
                                    )
                                }}
                            </DropdownLink>
                            <DropdownLink
                                v-if="isAdmin"
                                :href="route('admin.index')"
                            >
                                {{
                                    $t(
                                        "components.navbar.profileDropdown.admin",
                                    )
                                }}
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post">
                                {{
                                    $t(
                                        "components.navbar.profileDropdown.logout",
                                    )
                                }}
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </template>
                <template v-else>
                    <a :href="route('login')">
                        <OhVueIcon name="io-person" scale="1.7" fill="#025259">
                        </OhVueIcon>
                    </a>
                </template>
            </div>

            <HamburgerMenu :options="options" />
        </div>
    </nav>
</template>
