<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from "vue";
import { Link } from "@inertiajs/vue3";
import { route, type HasQueryParam } from "ziggy-js";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import type Competition from "@/Types/Competition";
import type { User } from "@/Types/User";
import PillSelector from "@/Components/UI/PillSelector.vue";
import { Menu, X, ChevronRight, ChevronDown, ArrowRight } from "@lucide/vue";

interface RouteItem {
    label: string;
    href?: string;
    _query?: HasQueryParam["_query"];
}

type Routes = Record<string, RouteItem>;

const props = defineProps<{
    modelValue?: boolean;
    options: {
        pages: Routes;
        homeSections?: Routes;
        competitions: Competition[];
        editions: number[];
        currentEdition?: number;
    };
    user?: User;
    isAdmin?: boolean;
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: boolean): void;
}>();

const internalOpen = ref(props.modelValue ?? false);
const editionsOpen = ref(false);

watch(
    () => props.modelValue,
    (val) => {
        if (val !== undefined && val !== internalOpen.value) {
            internalOpen.value = val;
        }
    },
);

watch(internalOpen, (val) => {
    emit("update:modelValue", val);
    if (!val) {
        editionsOpen.value = false;
    }
    if (typeof document !== "undefined") {
        document.body.classList.toggle("overflow-hidden", val);
    }
});

const closeMenu = () => {
    internalOpen.value = false;
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === "Escape" && internalOpen.value) {
        closeMenu();
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeyDown);
    if (typeof document !== "undefined") {
        document.body.classList.remove("overflow-hidden");
    }
});

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
</script>

<template>
    <PillSelector size="sm" :wrap="false" class="md:hidden">
        <button
            type="button"
            class="pill-item flex h-7 w-7 items-center justify-center p-0"
            aria-label="Menu"
            @click="internalOpen = !internalOpen"
        >
            <Menu :size="16" />
        </button>
    </PillSelector>

    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div
                v-if="internalOpen"
                class="bg-sinf-surface-dark/95 fixed inset-0 z-100 flex flex-col justify-between overflow-y-auto p-5 pt-4 shadow-2xl backdrop-blur-3xl md:hidden"
            >
                <div>
                    <div
                        class="flex items-center justify-between border-b border-white/8 pb-4"
                    >
                        <Link
                            :href="route('home')"
                            class="flex items-center gap-2.5"
                            @click="closeMenu"
                        >
                            <img
                                class="h-6 w-auto"
                                src="/images/sinf2026.svg"
                                alt="SINF Logo"
                            />
                        </Link>

                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/8 bg-white/4 text-neutral-300 transition-colors hover:bg-white/8 hover:text-white focus:outline-none"
                            aria-label="Fechar menu"
                            @click="closeMenu"
                        >
                            <X :size="20" />
                        </button>
                    </div>

                    <div class="mt-5 space-y-6">
                        <div>
                            <div
                                class="px-2 pb-2 text-[11px] font-semibold tracking-wider text-neutral-500 uppercase"
                            >
                                Navegação
                            </div>
                            <div class="space-y-1">
                                <template
                                    v-for="(
                                        { label, href }, key
                                    ) in options.homeSections"
                                    :key="key"
                                >
                                    <ResponsiveNavLink
                                        :href="href || '/'"
                                        @click="closeMenu"
                                    >
                                        <span>{{ label }}</span>
                                        <ChevronRight
                                            :size="16"
                                            class="text-neutral-500"
                                        />
                                    </ResponsiveNavLink>
                                </template>

                                <template
                                    v-for="(
                                        { label, _query }, pageKey
                                    ) in options.pages"
                                    :key="pageKey"
                                >
                                    <ResponsiveNavLink
                                        :href="getPageRoute(pageKey, _query)"
                                        :active="isCurrentRoute(pageKey)"
                                        @click="closeMenu"
                                    >
                                        <span>{{ label }}</span>
                                        <ChevronRight
                                            :size="16"
                                            class="text-neutral-500"
                                        />
                                    </ResponsiveNavLink>
                                </template>
                            </div>
                        </div>

                        <div v-if="options.competitions.length > 0">
                            <div
                                class="px-2 pb-2 text-[11px] font-semibold tracking-wider text-neutral-500 uppercase"
                            >
                                Competições
                            </div>
                            <div class="space-y-1">
                                <template
                                    v-for="comp in options.competitions"
                                    :key="comp.id"
                                >
                                    <ResponsiveNavLink
                                        :href="
                                            route('competition.show', {
                                                competition: comp.slug,
                                            })
                                        "
                                        @click="closeMenu"
                                    >
                                        <span>{{ comp.name }}</span>
                                        <ChevronRight
                                            :size="16"
                                            class="text-neutral-500"
                                        />
                                    </ResponsiveNavLink>
                                </template>
                            </div>
                        </div>

                        <div>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg px-2 py-1 text-[11px] font-semibold tracking-wider text-neutral-500 uppercase transition-colors hover:text-neutral-300 focus:outline-none"
                                @click="editionsOpen = !editionsOpen"
                            >
                                <span>Edições Anteriores</span>
                                <ChevronDown
                                    :size="14"
                                    class="transition-transform duration-200"
                                    :class="{ 'rotate-180': editionsOpen }"
                                />
                            </button>
                            <div
                                v-if="editionsOpen"
                                class="mt-2 grid grid-cols-3 gap-2"
                            >
                                <template
                                    v-for="edition in options.editions"
                                    :key="`mobile-edition-${edition}`"
                                >
                                    <a
                                        v-if="
                                            edition !==
                                            (options.currentEdition || 2026)
                                        "
                                        :href="`https://${edition}.sinf.pt`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center justify-center gap-1 rounded-xl border border-white/6 bg-white/3 py-2 text-xs font-medium text-neutral-300 transition-colors hover:border-white/20 hover:bg-white/8 hover:text-white"
                                    >
                                        <span>{{ edition }}</span>
                                    </a>
                                    <div
                                        v-else
                                        class="flex items-center justify-center gap-1.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 py-2 text-xs font-semibold text-emerald-300"
                                    >
                                        <span>{{ edition }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-white/8 pt-4">
                    <template v-if="user">
                        <div
                            class="mb-3 flex items-center gap-3 rounded-2xl border border-white/8 bg-white/3 p-3"
                        >
                            <img
                                :src="user.profile_photo_url"
                                :alt="user.name"
                                class="h-10 w-10 rounded-full object-cover ring-2 ring-white/10"
                            />
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-semibold text-white"
                                >
                                    {{ user.name }}
                                </p>
                                <p class="truncate text-xs text-neutral-400">
                                    {{ user.email }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <Link
                                :href="route('profile.show')"
                                class="flex items-center justify-center gap-2 rounded-xl border border-white/8 bg-white/4 py-2.5 text-xs font-medium text-white transition-colors hover:bg-white/8"
                                @click="closeMenu"
                            >
                                <span>Perfil</span>
                            </Link>
                            <Link
                                v-if="isAdmin"
                                :href="route('admin.index')"
                                class="flex items-center justify-center gap-2 rounded-xl border border-amber-500/30 bg-amber-500/10 py-2.5 text-xs font-medium text-amber-300 transition-colors hover:bg-amber-500/20"
                                @click="closeMenu"
                            >
                                <span>Admin</span>
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex items-center justify-center gap-2 rounded-xl border border-red-500/20 bg-red-500/10 py-2.5 text-xs font-medium text-red-300 transition-colors hover:bg-red-500/20"
                                :class="{ 'col-span-2': isAdmin }"
                                @click="closeMenu"
                            >
                                <span>Logout</span>
                            </Link>
                        </div>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-white py-3 text-sm font-semibold text-black shadow-lg transition-transform active:scale-98"
                            @click="closeMenu"
                        >
                            <span>Entrar</span>
                            <ArrowRight :size="16" />
                        </Link>
                    </template>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
