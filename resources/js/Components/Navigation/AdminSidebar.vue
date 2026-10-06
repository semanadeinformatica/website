<script setup lang="ts">
import { computed, type Component } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import {
    Calendar,
    CalendarDays,
    Presentation,
    Tag,
    Clock,
    Users,
    UserCheck,
    Trophy,
    Users2,
    Award,
    Building2,
    Crown,
    Store,
    ShoppingBag,
    CheckSquare,
    Briefcase,
    UserCog,
    LayoutDashboard,
    ArrowLeft,
    LogOut,
    X,
} from "@lucide/vue";

interface Props {
    open?: boolean;
    mobileOpen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
    mobileOpen: false,
});

const emit = defineEmits<{
    (e: "update:open", value: boolean): void;
    (e: "update:mobileOpen", value: boolean): void;
    (e: "close"): void;
}>();

const isOpen = computed(() => Boolean(props.open || props.mobileOpen));

const close = () => {
    emit("update:open", false);
    emit("update:mobileOpen", false);
    emit("close");
};

interface NavItem {
    name: string;
    route: string;
    routePrefix: string;
    labelKey?: string;
    defaultLabel: string;
    icon: Component;
}

interface NavSection {
    id: string;
    title: string;
    items: NavItem[];
}

const sections: NavSection[] = [
    {
        id: "events",
        title: "Programa & Eventos",
        items: [
            {
                name: "editions",
                route: "admin.editions.index",
                routePrefix: "admin.editions",
                labelKey: "models.edition",
                defaultLabel: "Edições",
                icon: Calendar,
            },
            {
                name: "eventDays",
                route: "admin.eventDays.index",
                routePrefix: "admin.eventDays",
                labelKey: "models.eventDay",
                defaultLabel: "Dias do Evento",
                icon: CalendarDays,
            },
            {
                name: "events",
                route: "admin.events.index",
                routePrefix: "admin.events",
                labelKey: "models.event",
                defaultLabel: "Atividades",
                icon: Presentation,
            },
            {
                name: "eventTypes",
                route: "admin.eventTypes.index",
                routePrefix: "admin.eventTypes",
                labelKey: "models.eventType",
                defaultLabel: "Tipos de Atividade",
                icon: Tag,
            },
            {
                name: "slots",
                route: "admin.slots.index",
                routePrefix: "admin.slots",
                labelKey: "models.slot",
                defaultLabel: "Encaixes (Slots)",
                icon: Clock,
            },
        ],
    },
    {
        id: "participants",
        title: "Participantes & Acessos",
        items: [
            {
                name: "users",
                route: "admin.users.index",
                routePrefix: "admin.users",
                labelKey: "models.user",
                defaultLabel: "Utilizadores",
                icon: Users,
            },
            {
                name: "enrollments",
                route: "admin.enrollments.index",
                routePrefix: "admin.enrollments",
                labelKey: "models.enrollment",
                defaultLabel: "Inscrições",
                icon: UserCheck,
            },
        ],
    },
    {
        id: "competitions",
        title: "Competições",
        items: [
            {
                name: "competitions",
                route: "admin.competitions.index",
                routePrefix: "admin.competitions",
                labelKey: "models.competition",
                defaultLabel: "Competições",
                icon: Trophy,
            },
            {
                name: "competitionTeams",
                route: "admin.competitionTeams.index",
                routePrefix: "admin.competitionTeams",
                labelKey: "models.competitionTeam",
                defaultLabel: "Equipas",
                icon: Users2,
            },
            {
                name: "competitionPrizes",
                route: "admin.competitionPrizes.index",
                routePrefix: "admin.competitionPrizes",
                labelKey: "models.competitionPrize",
                defaultLabel: "Prémios",
                icon: Award,
            },
        ],
    },
    {
        id: "companies",
        title: "Empresas & Patrocínios",
        items: [
            {
                name: "sponsors",
                route: "admin.sponsors.index",
                routePrefix: "admin.sponsors",
                labelKey: "models.sponsor",
                defaultLabel: "Patrocinadores",
                icon: Building2,
            },
            {
                name: "sponsorTiers",
                route: "admin.sponsorTiers.index",
                routePrefix: "admin.sponsorTiers",
                labelKey: "models.sponsorTier",
                defaultLabel: "Níveis de Patrocínio",
                icon: Crown,
            },
            {
                name: "stands",
                route: "admin.stands.index",
                routePrefix: "admin.stands",
                labelKey: "models.stand",
                defaultLabel: "Bancas",
                icon: Store,
            },
        ],
    },
    {
        id: "gamification",
        title: "Loja & Gamificação",
        items: [
            {
                name: "products",
                route: "admin.products.index",
                routePrefix: "admin.products",
                labelKey: "models.product",
                defaultLabel: "Produtos",
                icon: ShoppingBag,
            },
            {
                name: "quests",
                route: "admin.quests.index",
                routePrefix: "admin.quests",
                labelKey: "models.quest",
                defaultLabel: "Tarefas",
                icon: CheckSquare,
            },
        ],
    },
    {
        id: "organization",
        title: "Organização",
        items: [
            {
                name: "departments",
                route: "admin.departments.index",
                routePrefix: "admin.departments",
                labelKey: "models.department",
                defaultLabel: "Departamentos",
                icon: Briefcase,
            },
            {
                name: "staff",
                route: "admin.staff.index",
                routePrefix: "admin.staff",
                labelKey: "models.staff",
                defaultLabel: "Staff",
                icon: UserCog,
            },
        ],
    },
];

const currentRouteName = computed(() => {
    try {
        return route().current() ?? "";
    } catch {
        return "";
    }
});

const isItemActive = (item: NavItem) => {
    if (!currentRouteName.value) return false;
    return (
        currentRouteName.value === item.route ||
        currentRouteName.value.startsWith(`${item.routePrefix}.`)
    );
};

const isDashboardActive = computed(() => {
    return currentRouteName.value === "admin.index";
});
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm transition-opacity md:hidden"
        @click="close"
    />

    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 flex h-screen w-72 flex-col overflow-hidden border-r border-white/10 bg-[#0d0e12]/95 backdrop-blur-2xl transition-transform duration-300 ease-in-out md:static md:z-30 md:w-64 md:shrink-0 md:translate-x-0 md:bg-[#0d0e12]/80',
            isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
        ]"
    >
        <div
            class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-5"
        >
            <Link
                :href="route('admin.index')"
                class="flex items-center gap-3 transition-opacity hover:opacity-90"
                @click="close"
            >
                <img
                    src="/images/sinf-logo-generic-long.svg"
                    alt="SINF Logo"
                    class="h-7 w-auto"
                />
            </Link>

            <button
                type="button"
                class="pill-container flex h-8 w-8 cursor-pointer items-center justify-center text-neutral-400 transition-colors hover:text-white md:hidden"
                aria-label="Fechar menu"
                @click="close"
            >
                <X :size="15" />
            </button>
        </div>

        <div class="no-scrollbar flex-1 space-y-6 overflow-y-auto px-3.5 py-4">
            <div>
                <Link
                    :href="route('admin.index')"
                    :class="[
                        'pill-item w-full justify-start gap-2.5 rounded-full px-3 py-2 text-xs transition-all duration-150',
                        isDashboardActive
                            ? 'pill-item-active text-white'
                            : 'text-neutral-400 hover:text-white',
                    ]"
                    @click="close"
                >
                    <LayoutDashboard
                        :size="15"
                        :class="
                            isDashboardActive
                                ? 'text-white'
                                : 'text-neutral-400'
                        "
                    />
                    <span>Visão Geral</span>
                </Link>
            </div>

            <div
                v-for="section in sections"
                :key="section.id"
                class="space-y-1.5"
            >
                <div
                    class="px-3 text-[10px] font-semibold tracking-wider text-neutral-500 uppercase"
                >
                    {{ section.title }}
                </div>

                <div class="space-y-0.5">
                    <Link
                        v-for="item in section.items"
                        :key="item.name"
                        :href="route(item.route)"
                        :class="[
                            'pill-item w-full justify-start gap-2.5 rounded-full px-3 py-1.5 text-xs transition-all duration-150',
                            isItemActive(item)
                                ? 'pill-item-active text-white'
                                : 'text-neutral-400 hover:text-white',
                        ]"
                        @click="close"
                    >
                        <component
                            :is="item.icon"
                            :size="14"
                            :class="
                                isItemActive(item)
                                    ? 'text-white'
                                    : 'text-neutral-400'
                            "
                        />
                        <span class="truncate">
                            {{
                                item.labelKey
                                    ? $t(item.labelKey, 2)
                                    : item.defaultLabel
                            }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>

        <div class="shrink-0 border-t border-white/10 p-3.5">
            <div
                v-if="$page.props.auth?.user"
                class="mb-3 flex items-center gap-2.5 rounded-2xl border border-white/5 bg-white/2 p-2"
            >
                <img
                    :src="$page.props.auth.user.profile_photo_url"
                    :alt="$page.props.auth.user.name"
                    class="h-7 w-7 shrink-0 rounded-full object-cover ring-1 ring-white/20"
                />
                <div class="min-w-0 flex-1">
                    <div
                        class="truncate text-xs font-semibold text-white"
                        :title="$page.props.auth.user.name"
                    >
                        {{ $page.props.auth.user.name }}
                    </div>
                    <div
                        class="truncate text-[10px] text-neutral-400"
                        :title="$page.props.auth.user.email"
                    >
                        {{ $page.props.auth.user.email }}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    :href="route('home')"
                    class="pill-item flex-1 justify-center gap-1.5 px-3 py-1.5 text-xs text-neutral-300 hover:text-white"
                    title="Voltar ao site público"
                >
                    <ArrowLeft :size="13" />
                    <span>Voltar ao site</span>
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="pill-item justify-center p-2 text-neutral-400 hover:bg-red-500/20 hover:text-red-400"
                    title="Terminar sessão"
                >
                    <LogOut :size="13" />
                </Link>
            </div>
        </div>
    </aside>
</template>
