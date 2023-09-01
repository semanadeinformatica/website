<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from "vue";

interface Props {
    align: "left" | "right" | "center";
    width: string | number;
    contentClasses: string[];
}

const props = withDefaults(defineProps<Props>(), {
    align: "right",
    width: "48",
    contentClasses: () => ["bg-2023-red-dark", "text-center"],
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

const alignmentClasses = computed(() => {
    if (props.align === "left") return "origin-top-left left-0";

    if (props.align === "center") {
        return "origin-top left-1/2 -translate-x-1/2";
    }

    if (props.align === "right") {
        return "origin-top-right right-0";
    }

    return "origin-top";
});
</script>

<template>
    <div class="relative justify-center">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            class="fixed z-40 justify-center"
            @click="open = false"
        />

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 -translate-y-6"
            enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 translate-y-0"
            leave-to-class="transform opacity-0 -translate-y-6"
        >
            <div
                v-show="open"
                class="absolute z-50"
                :class="[widthClass, alignmentClasses]"
                style="display: none"
                @click="open = false"
            >
                <div :class="contentClasses">
                    <slot name="content" />
                </div>
            </div>
        </transition>
    </div>
</template>
