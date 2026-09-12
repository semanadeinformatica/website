<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { CheckCircle, AlertTriangle, X } from "@lucide/vue";

const show = ref(true);
const style = computed(
    () => usePage().props.jetstream.flash?.bannerStyle || "success",
);
const message = computed(() => usePage().props.jetstream.flash?.banner || "");
const id = computed(() => usePage().props.jetstream.flash?.bannerId || "");

watch(id, async () => {
    show.value = true;
});
</script>

<template>
    <div>
        <div
            v-if="show && message"
            class="border-b backdrop-blur-md transition-all"
            :class="{
                'border-emerald-500/30 bg-emerald-950/80 text-emerald-200':
                    style == 'success',
                'border-red-500/30 bg-red-950/80 text-red-200':
                    style == 'danger',
            }"
        >
            <div
                class="mx-auto flex max-w-(--breakpoint-xl) items-center justify-between gap-4 px-4 py-2.5"
            >
                <div class="flex items-center gap-3">
                    <CheckCircle
                        v-if="style == 'success'"
                        :size="20"
                        class="shrink-0 text-emerald-400"
                    />
                    <AlertTriangle
                        v-else
                        :size="20"
                        class="shrink-0 text-red-400"
                    />

                    <span class="text-sm font-medium">
                        {{ message }}
                    </span>
                </div>

                <button
                    type="button"
                    class="pill-container ml-auto flex h-7 w-7 cursor-pointer items-center justify-center text-neutral-400 transition-colors hover:text-white focus-visible:ring-2 focus-visible:ring-white/20"
                    aria-label="Dismiss"
                    @click.prevent="show = false"
                >
                    <X :size="14" />
                </button>
            </div>
        </div>
    </div>
</template>
