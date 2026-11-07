<script setup lang="ts">
import type CompetitionTeam from "@/Types/CompetitionTeam";
import ComingSoon from "@/Components/UI/ComingSoon.vue";

interface Props {
    teams?: CompetitionTeam[];
}

const props = withDefaults(defineProps<Props>(), {
    teams: () => [],
});
</script>

<template>
    <section class="w-full">
        <div
            v-if="props.teams.length > 0"
            class="overflow-hidden rounded-3xl border border-white/8 bg-black/50 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md"
        >
            <div class="divide-y divide-white/5">
                <div
                    v-for="(team, index) in props.teams"
                    :key="team.id"
                    class="group flex items-center justify-between gap-4 px-5 py-4 transition-colors duration-150 hover:bg-white/4 sm:px-6 sm:py-4.5"
                >
                    <div class="flex min-w-0 items-center gap-4 sm:gap-6">
                        <span
                            class="w-6 shrink-0 font-mono text-xs font-bold sm:w-8 sm:text-sm"
                            :class="[
                                index === 0
                                    ? 'text-amber-300'
                                    : index === 1
                                      ? 'text-slate-300'
                                      : index === 2
                                        ? 'text-amber-500'
                                        : 'text-neutral-500',
                            ]"
                        >
                            #{{ index + 1 }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <h4
                                class="truncate text-sm font-bold text-white transition-colors group-hover:text-neutral-200 sm:text-base"
                            >
                                {{ team.name }}
                            </h4>
                            <p
                                v-if="team.members && team.members.length > 0"
                                class="mt-0.5 truncate text-xs text-neutral-400"
                            >
                                {{
                                    team.members
                                        .map((m) => m.user?.name)
                                        .filter(Boolean)
                                        .join(", ")
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <span class="text-sm font-bold text-white sm:text-base">
                            {{ team.points }}
                        </span>
                        <span class="ml-1 text-xs text-neutral-400">pts</span>
                    </div>
                </div>
            </div>
        </div>

        <ComingSoon v-else class="py-20" />
    </section>
</template>
