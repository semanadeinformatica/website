<script setup lang="ts">
interface Props {
    as?: string;
    interactive?: boolean;
    imageSrc?: string;
    imageAlt?: string;
    imageAspectRatio?: string;
    imageFit?: "object-cover" | "object-contain" | "object-fill" | string;
    padding?: string;
    hasOverlay?: boolean;
}

withDefaults(defineProps<Props>(), {
    as: "article",
    interactive: true,
    imageSrc: undefined,
    imageAlt: "",
    imageAspectRatio: "aspect-square",
    imageFit: "object-cover",
    padding: "p-4 sm:p-5",
    hasOverlay: true,
});
</script>

<template>
    <component
        :is="as"
        class="group relative flex flex-col justify-between rounded-3xl border border-white/8 bg-black/50 text-left shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none"
        :class="[
            padding,
            interactive
                ? 'hover:scale-[1.015] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)]'
                : '',
        ]"
    >
        <div class="flex flex-1 flex-col">
            <!-- Media / Top Image -->
            <div
                v-if="imageSrc || $slots.image"
                class="relative w-full overflow-hidden rounded-2xl bg-neutral-900 ring-1 ring-white/10"
                :class="imageAspectRatio"
            >
                <slot name="image">
                    <img
                        v-if="imageSrc"
                        :src="imageSrc"
                        :alt="imageAlt"
                        class="h-full w-full transition-transform duration-500 group-hover:scale-105"
                        :class="imageFit"
                    />
                </slot>

                <div
                    v-if="hasOverlay"
                    class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                />

                <slot name="image-overlay" />
            </div>

            <!-- Header Slot -->
            <header
                v-if="$slots.header"
                :class="{ 'mt-4': imageSrc || $slots.image }"
            >
                <slot name="header" />
            </header>

            <!-- Main Content Slot -->
            <div
                v-if="$slots.default"
                class="flex-1"
                :class="{
                    'mt-3': $slots.header || imageSrc || $slots.image,
                }"
            >
                <slot />
            </div>
        </div>

        <!-- Footer Slot -->
        <footer
            v-if="$slots.footer"
            class="mt-4 flex min-h-9 items-center justify-between pt-3"
        >
            <slot name="footer" />
        </footer>
    </component>
</template>
