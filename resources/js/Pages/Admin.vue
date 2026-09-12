<script setup lang="ts">
import type { Component } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
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
    ArrowUpRight,
    ExternalLink,
    Plus,
} from "@lucide/vue";

interface Stats {
    users_count: number;
    enrollments_count: number;
    events_count: number;
    event_days_count: number;
    sponsors_count: number;
    stands_count: number;
    products_count: number;
    quests_count: number;
    competitions_count: number;
}

interface Edition {
    id: number;
    name: string;
    year: string;
}

defineProps<{
    currentEdition?: Edition | null;
    stats?: Stats;
}>();

interface NavItem {
    route: string;
    name: string;
    icon: Component;
}

interface Section {
    title: string;
    items: NavItem[];
}

const sections: Section[] = [
    {
        title: "Programa & Eventos",
        items: [
            { route: "admin.editions.index", name: "Edições", icon: Calendar },
            { route: "admin.eventDays.index", name: "Dias de Evento", icon: CalendarDays },
            { route: "admin.events.index", name: "Eventos & Sessões", icon: Presentation },
            { route: "admin.eventTypes.index", name: "Tipos de Evento", icon: Tag },
            { route: "admin.slots.index", name: "Encaixes (Slots)", icon: Clock },
        ],
    },
    {
        title: "Participantes & Inscrições",
        items: [
            { route: "admin.users.index", name: "Utilizadores", icon: Users },
            { route: "admin.enrollments.index", name: "Inscrições", icon: UserCheck },
        ],
    },
    {
        title: "Competições",
        items: [
            { route: "admin.competitions.index", name: "Competições", icon: Trophy },
            { route: "admin.competitionTeams.index", name: "Equipas", icon: Users2 },
            { route: "admin.competitionPrizes.index", name: "Prémios", icon: Award },
        ],
    },
    {
        title: "Empresas & Patrocínios",
        items: [
            { route: "admin.sponsors.index", name: "Patrocinadores", icon: Building2 },
            { route: "admin.sponsorTiers.index", name: "Níveis de Patrocínio", icon: Crown },
            { route: "admin.stands.index", name: "Bancas", icon: Store },
        ],
    },
    {
        title: "Loja & Gamificação",
        items: [
            { route: "admin.products.index", name: "Produtos da Loja", icon: ShoppingBag },
            { route: "admin.quests.index", name: "Tarefas & Missões", icon: CheckSquare },
        ],
    },
    {
        title: "Organização",
        items: [
            { route: "admin.departments.index", name: "Departamentos", icon: Briefcase },
            { route: "admin.staff.index", name: "Equipa & Staff", icon: UserCog },
        ],
    },
];
</script>

<template>
    <AdminLayout title="Painel de Administração">
        <div class="flex flex-col gap-10">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        {{ currentEdition?.name ?? "Edição Atual" }}
                    </h1>

                <div class="flex flex-wrap items-center gap-2.5">
                    <PrimaryButton
                        color="pill"
                        text-size="text-xs"
                        :href="route('admin.events.create')"
                    >
                        <Plus :size="13" />
                        <span>Novo Evento</span>
                    </PrimaryButton>
                    <PrimaryButton
                        color="pill"
                        text-size="text-xs"
                        :href="route('admin.users.create')"
                    >
                        <Plus :size="13" />
                        <span>Novo Utilizador</span>
                    </PrimaryButton>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <div
                    class="rounded-3xl border border-white/8 bg-black/50 p-6 backdrop-blur-md transition-colors hover:border-white/15"
                >
                    <div class="text-3xl font-bold text-white sm:text-4xl">
                        {{ stats?.enrollments_count ?? 0 }}
                    </div>
                    <div
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        Inscrições
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-white/8 bg-black/50 p-6 backdrop-blur-md transition-colors hover:border-white/15"
                >
                    <div class="text-3xl font-bold text-white sm:text-4xl">
                        {{ stats?.users_count ?? 0 }}
                    </div>
                    <div
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        Utilizadores
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-white/8 bg-black/50 p-6 backdrop-blur-md transition-colors hover:border-white/15"
                >
                    <div class="text-3xl font-bold text-white sm:text-4xl">
                        {{ stats?.events_count ?? 0 }}
                    </div>
                    <div
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        Eventos
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-white/8 bg-black/50 p-6 backdrop-blur-md transition-colors hover:border-white/15"
                >
                    <div class="text-3xl font-bold text-white sm:text-4xl">
                        {{ stats?.sponsors_count ?? 0 }}
                    </div>
                    <div
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        Empresas
                    </div>
                </div>

                <div
                    class="col-span-2 rounded-3xl border border-white/8 bg-black/50 p-6 backdrop-blur-md transition-colors hover:border-white/15 sm:col-span-1"
                >
                    <div class="text-3xl font-bold text-white sm:text-4xl">
                        {{ stats?.competitions_count ?? 0 }}
                    </div>
                    <div
                        class="mt-2 font-mono text-xs font-medium tracking-wider text-neutral-400 uppercase"
                    >
                        Competições
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <h2 class="text-lg font-bold text-white">Acessos Rápidos</h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="section in sections"
                        :key="section.title"
                        class="rounded-3xl border border-white/8 bg-black/40 p-5 backdrop-blur-md"
                    >
                        <h3
                            class="mb-3 font-mono text-xs font-semibold tracking-wider text-neutral-400 uppercase"
                        >
                            {{ section.title }}
                        </h3>

                        <div class="space-y-1.5">
                            <Link
                                v-for="item in section.items"
                                :key="item.route"
                                :href="route(item.route)"
                                class="group flex items-center justify-between rounded-xl px-3 py-2.5 text-sm text-neutral-300 transition-colors hover:bg-white/5 hover:text-white"
                            >
                                <div class="flex items-center gap-2.5">
                                    <component
                                        :is="item.icon"
                                        :size="15"
                                        class="text-neutral-400 transition-colors group-hover:text-white"
                                    />
                                    <span>{{ item.name }}</span>
                                </div>
                                <ArrowUpRight
                                    :size="13"
                                    class="text-neutral-600 transition-colors group-hover:text-neutral-300"
                                />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
