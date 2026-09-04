<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";

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
const triggerRef = ref<HTMLElement | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);
const dropdownStyle = ref<{
    top?: string;
    left?: string;
}>({});

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
    if (
        !triggerRef.value?.contains(target) &&
        !dropdownRef.value?.contains(target)
    ) {
        open.value = false;
    }
};

const widthPixelMap: Record<string, number> = {
    "20": 80,
    "32": 128,
    "40": 160,
    "44": 176,
    "48": 192,
    "52": 208,
    "56": 224,
    "60": 240,
    "64": 256,
    "72": 288,
};

const updatePosition = () => {
    if (!open.value || !triggerRef.value) return;

    const rect = triggerRef.value.getBoundingClientRect();
    const dropdownWidth =
        dropdownRef.value?.offsetWidth ||
        widthPixelMap[props.width.toString()] ||
        192;
    const docWidth = document.documentElement.clientWidth;

    let left = rect.left;
    if (props.align === "right") {
        left = rect.right - dropdownWidth;
    } else if (props.align === "center") {
        left = rect.left + (rect.width - dropdownWidth) / 2;
    }

    // Clamp horizontally to stay within viewport with 8px margin
    left = Math.max(8, Math.min(left, docWidth - dropdownWidth - 8));

    dropdownStyle.value = {
        top: `${Math.round(rect.bottom + 8)}px`,
        left: `${Math.round(left)}px`,
    };
};

watch(open, (isOpen) => {
    if (isOpen) {
        updatePosition();
        nextTick(() => {
            updatePosition();
        });
        window.addEventListener("scroll", updatePosition, { passive: true });
        window.addEventListener("resize", updatePosition, { passive: true });
    } else {
        window.removeEventListener("scroll", updatePosition);
        window.removeEventListener("resize", updatePosition);
    }
});

onMounted(() => {
    document.addEventListener("keydown", closeOnEscape);
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("scroll", updatePosition);
    window.removeEventListener("resize", updatePosition);
});

const widthClass = computed(() => {
    return (
        {
            "20": "w-20",
            "32": "w-32",
            "40": "w-40",
            "44": "w-44",
            "48": "w-48",
            "52": "w-52",
            "56": "w-56",
            "60": "w-60",
            "64": "w-64",
            "72": "w-72",
        }[props.width.toString()] || "w-48"
    );
});
</script>

<template>
    <div
        data-dropdown-root
        class="relative"
        @mouseenter="onMouseEnter"
        @mouseleave="onMouseLeave"
    >
        <div ref="triggerRef" @click="toggle">
            <slot name="trigger" :open="open" />
        </div>

        <Teleport to="body">
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
                    ref="dropdownRef"
                    class="glass-dropdown fixed z-100 before:absolute before:-top-2 before:left-0 before:h-2 before:w-full before:content-['']"
                    :class="widthClass"
                    :style="dropdownStyle"
                    @mouseenter="onMouseEnter"
                    @mouseleave="onMouseLeave"
                    @click="close"
                >
                    <div :class="contentClasses">
                        <slot name="content" :close="close" />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
