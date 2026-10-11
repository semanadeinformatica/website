<script setup lang="ts">
import type Slot from "@/Types/Slot";
import { computed } from "vue";
import { CheckCircle2 } from "@lucide/vue";

const props = defineProps<{
    sticker: Slot;
}>();

const sticker = computed(() => props.sticker);

const completedCount = computed(() => sticker.value.completed_count ?? 0);
const totalQuests = computed(() => sticker.value.total_quests ?? 1);

const completeness = computed(() => {
    return Math.min(1, completedCount.value / totalQuests.value);
});

const isFullyCompleted = computed(() => completeness.value >= 1);
</script>

<template>
    <div
        class="group relative flex w-56 flex-col items-center justify-between rounded-3xl border border-white/8 bg-black/50 p-4 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 select-none hover:scale-[1.02] hover:border-white/20 sm:w-60 sm:p-5"
    >
        <div
            class="relative flex h-44 w-44 items-center justify-center overflow-hidden"
            :style="
                sticker.image_slot_url
                    ? {
                          'mask-image': `url('${sticker.image_slot_url}')`,
                          'mask-repeat': 'no-repeat',
                          'mask-size': 'contain',
                          'mask-position': 'center',
                      }
                    : ''
            "
        >
            <img
                v-if="sticker.image_slot_url"
                class="absolute inset-0 h-full w-full object-contain transition-all duration-500 group-hover:scale-105"
                :src="sticker.image_slot_url"
                :alt="sticker.name"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center rounded-2xl bg-white/5 text-xs text-neutral-500"
            >
                Sem Imagem
            </div>

            <div
                class="pointer-events-none absolute inset-0 mix-blend-saturation"
                :style="{
                    'background-image': `conic-gradient(transparent ${completeness}turn, black ${completeness}turn 1turn)`,
                }"
            />

            <div
                class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 bg-black/75 p-3 text-center text-white opacity-0 transition-all duration-300 group-hover:opacity-100"
            >
                <span class="line-clamp-2 text-xs font-semibold">
                    {{ sticker.name }}
                </span>
                <span
                    class="flex items-center gap-1 text-xs font-bold text-amber-300"
                >
                    <span>+{{ sticker.points }}</span>
                    <img
                        class="h-3.5 w-3.5 object-contain"
                        title="SINFrão"
                        alt="SINFrão"
                        src="/images/sinf-2026-sm.svg"
                    />
                </span>
            </div>
        </div>

        <div
            class="mt-3 flex w-full flex-col gap-1.5 border-t border-white/8 pt-2"
        >
            <div class="flex items-center justify-between text-xs">
                <span
                    class="truncate font-medium text-neutral-200"
                    :title="sticker.name"
                >
                    {{ sticker.name }}
                </span>

                <span
                    v-if="isFullyCompleted"
                    class="ml-1 inline-flex shrink-0 items-center text-emerald-400"
                    title="Conquista concluída!"
                >
                    <CheckCircle2 :size="14" />
                </span>
            </div>

            <div
                class="flex items-center justify-between text-[11px] text-neutral-400"
            >
                <span
                    class="pill-container px-2 py-0.5 text-[10px] font-semibold text-neutral-300 shadow-none"
                >
                    {{ completedCount }} / {{ totalQuests }}
                </span>

                <span class="flex items-center gap-1 font-bold text-white">
                    <span>{{ sticker.points }}</span>
                    <img
                        class="h-3.5 w-3.5 object-contain"
                        alt="SINFrão"
                        title="SINFrão"
                        src="/images/sinf-2026-sm.svg"
                    />
                </span>
            </div>
        </div>
    </div>
</template>
