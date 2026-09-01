<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { route, type HasQueryParam } from "ziggy-js";
import { isAdmin as checkIsAdmin, type User } from "@/Types/User";
import type Competition from "@/Types/Competition";
import NavLink from "@/Components/NavLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownTrigger from "@/Components/DropdownTrigger.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import HamburgerMenu from "@/Components/HamburgerMenu.vue";

interface RouteItem {
    label: string;
    href?: string;
    _query?: HasQueryParam["_query"];
}

type Routes = Record<string, RouteItem>;

const homeSections: Routes = {
    aboutus: { label: "Sobre nós", href: "/#aboutus" },
    speakers: { label: "Oradores", href: "/#speakers" },
    sponsors: { label: "Patrocínios", href: "/#sponsors" },
};

const pageRoutes: Routes = {
    program: { label: "Programa" },
    "shop.show": { label: "Loja" },
    team: { label: "Equipa" },
};

const editions = [2026, 2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018];
const currentEdition = 2026;

const page = usePage();

const user = computed(() => page.props.auth?.user as User | undefined);
const isAdmin = computed(() => checkIsAdmin(user.value));
const competitions = computed<Competition[]>(
    () => (page.props.competitions as Competition[] | undefined) || [],
);

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

const options = computed(() => ({
    pages: pageRoutes,
    homeSections,
    competitions: competitions.value,
    editions,
    currentEdition,
}));

const handleScroll = () => {
    isScrolled.value = window.scrollY > 15;
};

const logout = () => {
    router.post(route("logout"));
};

const isCurrentRoute = (name: string) => {
    try {
        return route().current(name);
    } catch {
        return false;
    }
};

const getPageRoute = (name: string, query?: HasQueryParam["_query"]) => {
    try {
        return route(route().has(name) ? name : "home", { _query: query });
    } catch {
        return "/";
    }
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll, { passive: true });
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
    <header
        class="sticky top-0 z-50 w-full bg-transparent transition-all duration-300 ease-in-out"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
        >
            <div class="pill-container">
                <Link
                    :href="route('home')"
                    class="group flex items-center rounded-full px-2.5 py-1.5 transition-all duration-150 hover:bg-white/8 active:scale-95"
                >
                    <img
                        class="h-4 w-auto transition-all duration-200 group-hover:brightness-110 group-hover:drop-shadow-[0_0_12px_rgba(255,255,255,0.3)]"
                        src="/images/sinf-2026-sm.svg"
                        alt="SINF Logo"
                    />
                </Link>
            </div>

            <nav class="pill-container hidden gap-0.5 md:flex">
                <template
                    v-for="({ label, href }, key) in homeSections"
                    :key="key"
                >
                    <NavLink :href="href || '/'">
                        {{ label }}
                    </NavLink>
                </template>

                <template
                    v-for="({ label, _query }, pageKey) in pageRoutes"
                    :key="pageKey"
                >
                    <NavLink
                        :href="getPageRoute(pageKey, _query)"
                        :active="isCurrentRoute(pageKey)"
                    >
                        {{ label }}
                    </NavLink>
                </template>

                <Dropdown
                    v-if="competitions.length > 0"
                    align="center"
                    width="52"
                >
                    <template #trigger="{ open }">
                        <DropdownTrigger :active="open">
                            <span>Competições</span>
                            <svg
                                class="h-3.5 w-3.5 text-neutral-400 transition-transform duration-200"
                                :class="{ 'rotate-180 text-white': open }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </DropdownTrigger>
                    </template>

                    <template #content>
                        <template v-for="comp in competitions" :key="comp.id">
                            <DropdownLink
                                :href="
                                    route('competition.show', {
                                        competition: comp.slug,
                                    })
                                "
                            >
                                <span>{{ comp.name }}</span>
                                <svg
                                    class="h-3.5 w-3.5 text-neutral-500 opacity-0 transition-all duration-150 group-hover:translate-x-0.5 group-hover:text-white group-hover:opacity-100"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </DropdownLink>
                        </template>
                    </template>
                </Dropdown>
            </nav>

            <div class="flex items-center gap-2.5">
                <div class="pill-container hidden md:flex">
                    <Dropdown align="right" width="48">
                        <template #trigger="{ open }">
                            <button
                                type="button"
                                class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium text-neutral-300 transition-all duration-150 hover:bg-white/8 hover:text-white focus:outline-none"
                                :class="{
                                    'bg-white/8 text-white': open,
                                }"
                            >
                                <span>{{ currentEdition }}</span>
                                <svg
                                    class="h-3 w-3 text-neutral-400 transition-transform duration-200"
                                    :class="{ 'rotate-180 text-white': open }"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="p-1">
                                <div
                                    class="px-2.5 py-1 text-[10px] font-semibold text-neutral-500 uppercase"
                                >
                                    Edições Anteriores
                                </div>
                                <div class="mt-1 grid grid-cols-2 gap-1">
                                    <template
                                        v-for="edition in editions"
                                        :key="edition"
                                    >
                                        <a
                                            v-if="edition !== currentEdition"
                                            :href="`https://${edition}.sinf.pt`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-neutral-300 transition-colors hover:bg-white/8 hover:text-white"
                                        >
                                            <span>{{ edition }}</span>
                                        </a>
                                        <div
                                            v-else
                                            class="flex items-center justify-center gap-1 rounded-lg bg-white/8 px-2 py-1.5 text-xs font-semibold text-white"
                                        >
                                            <span>{{ edition }}</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </Dropdown>
                </div>

                <div v-if="user" class="pill-container">
                    <Dropdown align="right" width="56">
                        <template #trigger="{ open }">
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-full p-0.5 transition-all hover:bg-white/8 focus:outline-none"
                                :class="{ 'bg-white/8': open }"
                            >
                                <img
                                    class="h-7 w-7 rounded-full object-cover ring-1 ring-white/20 transition-all hover:ring-white/50"
                                    :src="user.profile_photo_url"
                                    :alt="user.name"
                                />
                            </button>
                        </template>

                        <template #content>
                            <div class="border-b border-white/8 px-3 py-2.5">
                                <p
                                    class="truncate text-xs font-semibold text-white"
                                >
                                    {{ user.name }}
                                </p>
                                <p
                                    class="truncate text-[11px] text-neutral-400"
                                >
                                    {{ user.email }}
                                </p>
                                <span
                                    v-if="isAdmin"
                                    class="mt-1.5 inline-block rounded-md border border-amber-500/30 bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-amber-300"
                                >
                                    Administrador
                                </span>
                            </div>

                            <div class="mt-1 space-y-0.5">
                                <DropdownLink :href="route('profile.show')">
                                    <span class="flex items-center gap-2.5">
                                        <svg
                                            class="h-4 w-4 text-neutral-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                        <span>Perfil</span>
                                    </span>
                                </DropdownLink>

                                <DropdownLink
                                    v-if="isAdmin"
                                    :href="route('admin.index')"
                                >
                                    <span class="flex items-center gap-2.5">
                                        <svg
                                            class="h-4 w-4 text-neutral-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                            />
                                        </svg>
                                        <span>Administração</span>
                                    </span>
                                </DropdownLink>

                                <div class="border-t border-white/6 pt-1">
                                    <button
                                        type="button"
                                        class="group flex w-full cursor-pointer items-center justify-between gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium text-red-400 transition-colors hover:bg-red-500/10 hover:text-red-300 focus:outline-none"
                                        @click="logout"
                                    >
                                        <span class="flex items-center gap-2.5">
                                            <svg
                                                class="h-4 w-4 text-red-400"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                                />
                                            </svg>
                                            <span>Logout</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </Dropdown>
                </div>

                <div v-else class="pill-container">
                    <Link
                        :href="route('login')"
                        class="group inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-medium text-neutral-300 transition-all duration-150 hover:bg-white/8 hover:text-white active:scale-95"
                    >
                        <span>Entrar</span>
                        <svg
                            class="h-3.5 w-3.5 text-neutral-400 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"
                            />
                        </svg>
                    </Link>
                </div>

                <HamburgerMenu
                    v-model="isMobileMenuOpen"
                    :options="options"
                    :user="user"
                    :is-admin="isAdmin"
                />
            </div>
        </div>
    </header>
</template>
