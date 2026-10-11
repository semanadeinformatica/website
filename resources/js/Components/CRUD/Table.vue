<script setup lang="ts" generic="T extends Model">
import type Model from "@/Types/Model";
import { Inbox } from "@lucide/vue";

defineProps<{
    items: T[];
}>();
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-white/10 bg-black/40 shadow-[0_4px_24px_rgba(0,0,0,0.3)] backdrop-blur-md"
    >
        <div class="w-full overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <slot name="header"></slot>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-neutral-200">
                    <template v-if="items.length > 0">
                        <template v-for="item in items" :key="item.id">
                            <slot name="row" :item="item"></slot>
                        </template>
                    </template>
                    <tr v-else>
                        <td :colspan="100" class="px-6 py-16 text-center">
                            <div
                                class="flex flex-col items-center justify-center gap-2"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-neutral-400"
                                >
                                    <Inbox :size="24" />
                                </div>
                                <span class="text-sm font-medium text-white"
                                    >Nenhum registo encontrado</span
                                >
                                <span class="text-xs text-neutral-400"
                                    >Tente ajustar a sua pesquisa ou os filtros
                                    ativos.</span
                                >
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
