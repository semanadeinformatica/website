<script setup lang="ts">
import { computed, ref, useAttrs, type ButtonHTMLAttributes } from "vue";
import { Link } from "@inertiajs/vue3";

interface Props {
    href?: string;
    external?: boolean;
    type?: ButtonHTMLAttributes["type"];
    color?: "pill" | "primary" | "secondary" | "gradient" | "danger";
    textSize?: string;
    padding?: string;
    disabled?: boolean;
    fullWidth?: boolean;
}

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const props = withDefaults(defineProps<Props>(), {
    href: undefined,
    external: false,
    type: "submit",
    color: "pill",
    textSize: "text-xs sm:text-sm",
    padding: "px-6 py-2",
    disabled: false,
    fullWidth: false,
});

const buttonRef = ref<HTMLButtonElement | HTMLAnchorElement | null>(null);

const isFullWidth = computed(() => {
    const cls = String(attrs.class ?? "");
    return (
        props.fullWidth ||
        cls.includes("w-full") ||
        cls.includes("flex-1")
    );
});

const innerWidthClass = computed(() => {
    if (!isFullWidth.value) return "";
    const cls = String(attrs.class ?? "");
    if (cls.includes("sm:w-auto") || cls.includes("sm:flex-initial")) {
        return "w-full sm:w-auto";
    }
    return "w-full";
});

const onContainerClick = (event: MouseEvent) => {
    if (
        event.target !== buttonRef.value &&
        buttonRef.value &&
        !props.disabled
    ) {
        buttonRef.value.click();
    }
};

const buttonColor: Record<string, string> = {
    pill: "pill-item-active hover:bg-white/20 text-white",
    primary:
        "bg-sinf-primary/80 hover:bg-sinf-primary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.4)]",
    secondary:
        "bg-sinf-secondary/80 hover:bg-sinf-secondary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(106,0,255,0.4)]",
    gradient:
        "bg-linear-to-r from-sinf-primary/90 to-sinf-secondary/90 hover:from-sinf-primary hover:to-sinf-secondary text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(154,0,54,0.35)]",
    danger: "bg-red-500/80 hover:bg-red-500 text-white hover:border-white/20 hover:shadow-[0_4px_20px_rgba(239,68,68,0.4)]",
};
</script>

<template>
    <div
        class="pill-container justify-center p-1.5 transition-all duration-200 select-none"
        :class="[
            isFullWidth ? 'flex w-full' : 'inline-flex',
            disabled ? 'cursor-not-allowed opacity-40' : 'cursor-pointer',
            attrs.class,
        ]"
        @click="onContainerClick"
    >
        <component
            :is="href ? (external ? 'a' : Link) : 'button'"
            ref="buttonRef"
            v-bind="{ ...attrs, class: undefined }"
            :href="href"
            :type="href ? undefined : type"
            :disabled="href ? undefined : disabled"
            class="pill-item cursor-pointer justify-center gap-2 font-medium transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
            :class="[
                innerWidthClass,
                buttonColor[color] ?? buttonColor.pill,
                textSize,
                padding,
            ]"
        >
            <slot />
        </component>
    </div>
</template>
