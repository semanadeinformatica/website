<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { usePage } from "@inertiajs/vue3";
import {
    FileText,
    Download,
    Users,
    Search,
    ExternalLink,
    X,
} from "@lucide/vue";
import { computed, ref } from "vue";
import SocialIcon from "@/Components/UI/SocialIcon.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import { route } from "ziggy-js";

type Visitor = Participant & {
    can_see_cv: boolean;
    can_see_linkedin: boolean;
    can_see_all: boolean;
};

const page = usePage();
const visitors = computed(() => (page.props.participants as Visitor[]) ?? []);
const user = computed(() => page.props.user as Participant);

const searchQuery = ref("");
const filterType = ref<"all" | "linkedin" | "cv">("all");

const cvCount = computed(
    () => visitors.value.filter((v) => v.can_see_cv && v.cv_url).length,
);

const linkedinCount = computed(
    () =>
        visitors.value.filter(
            (v) => v.can_see_linkedin && v.social_media?.linkedin,
        ).length,
);

const filterOptions = computed<PillOption[]>(() => [
    {
        id: "all",
        label: "Todos",
        count: visitors.value.length,
    },
    {
        id: "linkedin",
        label: "Com LinkedIn",
        count: linkedinCount.value,
    },
    {
        id: "cv",
        label: "Com CV",
        count: cvCount.value,
    },
]);

const filteredVisitors = computed(() => {
    return visitors.value.filter((v) => {
        if (filterType.value === "cv" && (!v.can_see_cv || !v.cv_url)) {
            return false;
        }
        if (
            filterType.value === "linkedin" &&
            (!v.can_see_linkedin || !v.social_media?.linkedin)
        ) {
            return false;
        }

        if (!searchQuery.value.trim()) return true;
        const q = searchQuery.value.toLowerCase().trim();
        const name = v.user?.name?.toLowerCase() ?? "";
        const email = v.user?.email?.toLowerCase() ?? "";
        return name.includes(q) || email.includes(q);
    });
});
</script>

<template>
    <div
        v-if="visitors.length > 0"
        class="flex w-full flex-col rounded-3xl border border-white/8 bg-black/50 p-5 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md sm:p-7"
    >
        <div
            class="flex flex-col gap-4 border-b border-white/8 pb-5 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-neutral-300"
                >
                    <Users :size="20" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-white sm:text-lg">
                        Participantes que visitaram a empresa
                    </h2>
                    <p class="text-xs text-neutral-400">
                        Total registado:
                        <span class="font-semibold text-white">{{
                            visitors.length
                        }}</span>
                    </p>
                </div>
            </div>

            <PrimaryButton
                :href="route('user.company.participants.cvs', { user })"
                external
                color="pill"
                padding="px-4 py-2"
                class="w-full sm:w-auto"
            >
                <Download :size="15" />
                <span>Descarregar CVs</span>
            </PrimaryButton>
        </div>

        <div
            class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="relative w-full sm:max-w-xs">
                <Search
                    :size="15"
                    class="absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400"
                />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Pesquisar por nome ou email..."
                    class="w-full rounded-2xl border border-white/10 bg-white/5 py-2 pr-8 pl-9 text-xs text-white placeholder-neutral-500 transition-colors focus:border-white/30 focus:bg-white/10 focus:outline-none sm:text-sm"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    class="absolute top-1/2 right-2.5 -translate-y-1/2 text-neutral-400 hover:text-white"
                    @click="searchQuery = ''"
                >
                    <X :size="14" />
                </button>
            </div>

            <PillSelector
                v-model="filterType"
                :items="filterOptions"
                size="sm"
            />
        </div>

        <div
            v-if="filteredVisitors.length > 0"
            class="mt-4 overflow-hidden rounded-2xl border border-white/8"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-neutral-300">
                    <tbody class="divide-y divide-white/6">
                        <tr
                            v-for="visitor in filteredVisitors"
                            :key="visitor.id"
                            class="transition-colors hover:bg-white/4"
                        >
                            <!-- Participant Info (Left) -->
                            <td class="py-3 pr-3 pl-4 sm:pl-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-neutral-800 ring-1 ring-white/10"
                                    >
                                        <img
                                            v-if="
                                                visitor.user?.profile_photo_url
                                            "
                                            :src="
                                                visitor.user.profile_photo_url
                                            "
                                            :alt="visitor.user.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <span
                                            v-else
                                            class="text-xs font-bold text-neutral-300 uppercase"
                                        >
                                            {{
                                                visitor.user?.name?.charAt(0) ??
                                                "P"
                                            }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <a
                                            :href="
                                                visitor.user
                                                    ? route('user.profile', {
                                                          user: visitor.user,
                                                      })
                                                    : '#'
                                            "
                                            target="_blank"
                                            class="block truncate font-medium text-white transition-colors hover:text-white/80"
                                        >
                                            {{
                                                visitor.user?.name ??
                                                `Participante #${visitor.id}`
                                            }}
                                        </a>
                                        <p
                                            v-if="visitor.user?.email"
                                            class="truncate text-xs text-neutral-400"
                                        >
                                            {{ visitor.user.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td
                                class="w-px py-3 pr-4 pl-3 text-right whitespace-nowrap sm:pr-5"
                            >
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <PrimaryButton
                                        v-if="
                                            visitor.can_see_linkedin &&
                                            visitor.social_media?.linkedin
                                        "
                                        :href="visitor.social_media.linkedin"
                                        external
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        color="pill"
                                        padding="p-2"
                                        class="shrink-0"
                                        title="LinkedIn"
                                        aria-label="LinkedIn"
                                    >
                                        <SocialIcon
                                            platform="linkedin"
                                            :size="15"
                                        />
                                    </PrimaryButton>

                                    <PrimaryButton
                                        v-if="
                                            visitor.can_see_cv && visitor.cv_url
                                        "
                                        :href="visitor.cv_url"
                                        external
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        color="pill"
                                        padding="p-2"
                                        class="shrink-0"
                                        title="Currículo Vitae"
                                        aria-label="Ver CV"
                                    >
                                        <FileText :size="15" />
                                    </PrimaryButton>

                                    <PrimaryButton
                                        v-if="visitor.user"
                                        :href="
                                            route('user.profile', {
                                                user: visitor.user,
                                            })
                                        "
                                        color="pill"
                                        padding="p-2"
                                        class="shrink-0"
                                        title="Ver Perfil"
                                        aria-label="Ver Perfil"
                                    >
                                        <ExternalLink :size="15" />
                                    </PrimaryButton>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="filteredVisitors.length === 0"
            class="flex flex-col items-center justify-center py-12 text-center"
        >
            <p class="text-sm text-neutral-400">
                Nenhum participante encontrado
                <span v-if="searchQuery"> para "{{ searchQuery }}"</span>
                <span v-if="filterType !== 'all'">
                    com o filtro selecionado</span
                >.
            </p>
            <button
                type="button"
                class="text-sinf-primary mt-2 text-xs hover:underline"
                @click="
                    searchQuery = '';
                    filterType = 'all';
                "
            >
                Limpar filtros
            </button>
        </div>
    </div>

    <div
        v-else
        class="flex flex-col items-center justify-center rounded-3xl border border-white/8 bg-black/50 px-6 py-16 text-center shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md"
    >
        <div
            class="mb-4 flex h-14 w-14 items-center justify-center rounded-3xl border border-white/10 bg-white/5 text-neutral-400"
        >
            <Users :size="28" />
        </div>
        <h3 class="text-base font-semibold text-white">
            Ainda sem visitas registadas
        </h3>
        <p class="mt-1 max-w-sm text-xs text-neutral-400 sm:text-sm">
            Os participantes que visitarem e fizerem check-in na vossa banca
            aparecerão aqui.
        </p>
    </div>
</template>
