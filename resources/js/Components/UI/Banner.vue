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
            class="border-b-2 border-black"
            :class="{
                'bg-2023-teal-dark': style == 'success',
                'bg-2023-red-dark': style == 'danger',
            }"
        >
            <div
                class="mx-auto flex max-w-(--breakpoint-xl) items-center justify-between gap-4 px-4 py-2"
            >
                <CheckCircle
                    v-if="style == 'success'"
                    :size="28"
                    class="shrink-0 text-white"
                />
                <AlertTriangle v-else :size="28" class="shrink-0 text-white" />

                <span class="font-medium text-white">
                    {{ message }}
                </span>

                <button
                    type="button"
                    class="ml-auto flex h-8 w-8 cursor-pointer items-center justify-center text-white hover:opacity-80 focus-visible:ring-2 focus-visible:ring-white/20"
                    aria-label="Dismiss"
                    @click.prevent="show = false"
                >
                    <X :size="20" />
                </button>
            </div>
        </div>
    </div>
</template>
