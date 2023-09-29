<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from "vue";

interface Props {
    width: string | number;
    contentClasses: string[];
}

const props = withDefaults(defineProps<Props>(), {
    width: "48",
    contentClasses: () => ["text-2023-red-dark", "text-center"],
});

let open = ref(false);

const closeOnEscape = (e: KeyboardEvent) => {
    if (open.value && e.key === "Escape") open.value = false;
};

onMounted(() => document.addEventListener("keydown", closeOnEscape));
onUnmounted(() => document.removeEventListener("keydown", closeOnEscape));

const widthClass = computed(() => {
    return {
        "20": "w-20",
        "32": "w-32",
        "48": "w-48",
    }[props.width.toString()];
});
</script>

<template>
    <div class="relative">
        <div class="h-full" v-on:mouseover="open = true" v-on:mouseleave="open = false">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            class="fixed z-40 justify-center"
            @click="open = false"
        />

        <transition>
            <div
                v-show="open"
                class="absolute left-1/2 z-50 -translate-x-1/2"
                :class="[widthClass]"
                style="display: none"
                v-on:mouseover="open = true" v-on:mouseleave="open = false"
                @click="open = false"
            >
                <div :class="contentClasses">
                    <slot name="content" />
                </div>
            </div>
        </transition>
    </div>
</template>
