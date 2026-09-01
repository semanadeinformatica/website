<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from "vue";

interface Props {
    align?: "left" | "right" | "center";
    width?: string | number;
    contentClasses?: string;
}

const props = withDefaults(defineProps<Props>(), {
    align: "center",
    width: "48",
    contentClasses: "p-1.5",
});

const open = ref(false);
let closeTimer: ReturnType<typeof setTimeout> | null = null;

const onMouseEnter = () => {
    if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
    }
    open.value = true;
};

const onMouseLeave = () => {
    closeTimer = setTimeout(() => {
        open.value = false;
    }, 150);
};

const toggle = () => {
    open.value = !open.value;
};

const close = () => {
    open.value = false;
};

const closeOnEscape = (e: KeyboardEvent) => {
    if (open.value && e.key === "Escape") open.value = false;
};

const handleClickOutside = (e: MouseEvent) => {
    const target = e.target as HTMLElement | null;
    if (!target?.closest("[data-dropdown-root]")) {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener("keydown", closeOnEscape);
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.removeEventListener("click", handleClickOutside);
});

const widthClass = computed(() => {
    return (
        {
            "20": "w-20",
            "32": "w-32",
            "48": "w-48",
            "52": "w-52",
            "56": "w-56",
            "64": "w-64",
        }[props.width.toString()] || "w-48"
    );
});

const alignmentClasses = computed(() => {
    if (props.align === "left") {
        return "left-0";
    } else if (props.align === "right") {
        return "right-0";
    }
    return "left-1/2 -translate-x-1/2";
});
</script>

<template>
    <div
        data-dropdown-root
        class="relative"
        @mouseenter="onMouseEnter"
        @mouseleave="onMouseLeave"
    >
        <div @click="toggle">
            <slot name="trigger" :open="open" />
        </div>

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-if="open"
                class="glass-dropdown absolute z-50 mt-2 ring-1 ring-white/5"
                :class="[widthClass, alignmentClasses]"
                @click="close"
            >
                <div :class="contentClasses">
                    <slot name="content" :close="close" />
                </div>
            </div>
        </Transition>
    </div>
</template>
