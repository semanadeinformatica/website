<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route, type HasQueryParam } from "ziggy-js";
import { isAdmin as checkIsAdmin, type User } from "@/Types/User";
import type Competition from "@/Types/Competition";
import DropdownLink from "@/Components/DropdownLink.vue";
import HamburgerMenu from "@/Components/HamburgerMenu.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import {
    ChevronRight,
    User as UserIcon,
    ShieldCheck,
    LogOut,
    ArrowRight,
} from "@lucide/vue";

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

const editions = [2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018];
const currentEdition = 2026;

const page = usePage();

const user = computed(() => page.props.auth?.user as User | undefined);
const isAdmin = computed(() => checkIsAdmin(user.value));
const competitions = computed<Competition[]>(
    () => (page.props.competitions as Competition[] | undefined) || [],
);

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

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

const logoItem = computed<PillOption[]>(() => [
    {
        id: "logo",
        href: route("home"),
        image: {
            src: "/images/sinf-2026-sm.svg",
            alt: "SINF Logo",
            class: "h-4 w-auto transition-all duration-200 hover:brightness-110",
        },
    },
]);

const navItems = computed<PillOption[]>(() => {
    const items: PillOption[] = [];
    for (const [key, { label, href }] of Object.entries(homeSections)) {
        items.push({
            id: `home-${key}`,
            label,
            href: href || "/",
        });
    }
    for (const [pageKey, { label, _query }] of Object.entries(pageRoutes)) {
        items.push({
            id: `page-${pageKey}`,
            label,
            href: getPageRoute(pageKey, _query),
            active: isCurrentRoute(pageKey),
        });
    }
    if (competitions.value.length > 0) {
        items.push({
            id: "competitions",
            label: "Competições",
            dropdown: {
                align: "center",
                width: 52,
                items: competitions.value.map((comp) => ({
                    id: comp.id,
                    label: comp.name,
                    href: route("competition.show", { competition: comp.slug }),
                    active:
                        isCurrentRoute("competition.show") &&
                        (page.props.competition as Competition | undefined)
                            ?.slug === comp.slug,
                    trailingIcon: ChevronRight,
                })),
            },
        });
    }
    return items;
});

const editionItem = computed<PillOption[]>(() => [
    {
        id: "edition",
        label: String(currentEdition),
        dropdown: {
            align: "right",
            width: 48,
        },
    },
]);

const authItems = computed<PillOption[]>(() => {
    if (user.value) {
        return [
            {
                id: "auth-user",
                ariaLabel: user.value.name,
                class: "flex h-7 w-7 items-center justify-center p-0 overflow-hidden",
                image: {
                    src: user.value.profile_photo_url,
                    alt: user.value.name,
                    class: "h-full w-full object-cover transition-all duration-200 hover:brightness-110",
                },
                dropdown: {
                    align: "right",
                    width: 56,
                },
            },
        ];
    }
    return [
        {
            id: "auth-login",
            label: "Entrar",
            href: route("login"),
            trailingIcon: ArrowRight,
        },
    ];
});

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
            <PillSelector :items="logoItem" size="sm" :wrap="false" />

            <PillSelector
                as="nav"
                :items="navItems"
                size="sm"
                :wrap="false"
                class="hidden md:flex"
            />

            <div class="flex items-center gap-2.5">
                <PillSelector
                    :items="editionItem"
                    size="sm"
                    :wrap="false"
                    class="hidden md:flex"
                >
                    <template #dropdown-edition>
                        <div>
                            <div
                                class="px-2.5 py-1 text-[10px] font-semibold tracking-wider text-neutral-500 uppercase"
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
                                        class="flex items-center justify-center gap-1 rounded-xl px-2 py-1.5 text-xs font-medium text-neutral-300 transition-all duration-150 hover:bg-white/8 hover:text-white active:scale-95"
                                    >
                                        <span>{{ edition }}</span>
                                    </a>
                                    <div
                                        v-else
                                        class="shadow-pill-active flex items-center justify-center gap-1 rounded-xl bg-white/14 px-2 py-1.5 text-xs font-semibold text-white"
                                    >
                                        <span>{{ edition }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </PillSelector>

                <PillSelector :items="authItems" size="sm" :wrap="false">
                    <template #dropdown-auth-user>
                        <div class="border-b border-white/8 px-3 py-2.5">
                            <p
                                class="truncate text-xs font-semibold text-white"
                            >
                                {{ user?.name }}
                            </p>
                            <p class="truncate text-[11px] text-neutral-400">
                                {{ user?.email }}
                            </p>
                        </div>

                        <div class="mt-1 space-y-0.5">
                            <DropdownLink
                                :href="route('profile.show')"
                                :active="isCurrentRoute('profile.show')"
                            >
                                <span class="flex items-center gap-2.5">
                                    <UserIcon
                                        :size="16"
                                        class="text-neutral-400 transition-colors group-hover:text-white"
                                    />
                                    <span>Perfil</span>
                                </span>
                            </DropdownLink>

                            <DropdownLink
                                v-if="isAdmin"
                                :href="route('admin.index')"
                                :active="isCurrentRoute('admin.index')"
                            >
                                <span class="flex items-center gap-2.5">
                                    <ShieldCheck
                                        :size="16"
                                        class="text-neutral-400 transition-colors group-hover:text-white"
                                    />
                                    <span>Administração</span>
                                </span>
                            </DropdownLink>

                            <div class="border-t border-white/8 pt-1">
                                <button
                                    type="button"
                                    class="group flex w-full cursor-pointer items-center justify-between gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium text-red-400 transition-all duration-150 hover:bg-red-500/10 hover:text-red-300 focus:outline-none active:scale-[0.98]"
                                    @click="logout"
                                >
                                    <span class="flex items-center gap-2.5">
                                        <LogOut
                                            :size="16"
                                            class="text-red-400 transition-colors group-hover:text-red-300"
                                        />
                                        <span>Logout</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </template>
                </PillSelector>

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
