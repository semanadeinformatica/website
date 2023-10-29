<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";

const show = ref(true);
const style = computed(
    () => usePage().props.jetstream.flash?.bannerStyle || "success",
);
const message = computed(() => usePage().props.jetstream.flash?.banner || "");
const id = computed(() => usePage().props.jetstream.flash?.bannerId || "");

watch(id, async () => {
    show.value = true;
});

console.log("Banner.vue");
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
                class="mx-auto flex max-w-screen-xl items-center justify-between gap-4 px-4 py-2"
            >
                <OhVueIcon
                    :name="
                        style == 'success'
                            ? 'io-checkmark-circle'
                            : 'io-warning'
                    "
                    fill="white"
                    class="h-8 w-8"
                />

                <span class="font-medium text-white">
                    {{ message }}
                </span>

                <button
                    type="button"
                    class="ml-auto flex h-8 w-8 items-center justify-center"
                    aria-label="Dismiss"
                    @click.prevent="show = false"
                >
                    <OhVueIcon name="io-close" fill="white" class="h-6 w-6" />
                </button>
            </div>
        </div>
    </div>
</template>
