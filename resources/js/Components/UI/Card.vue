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
    layout?: "vertical" | "horizontal" | "responsive";
}

withDefaults(defineProps<Props>(), {
    as: "article",
    interactive: true,
    imageSrc: undefined,
    imageAlt: "",
    imageAspectRatio: "aspect-square",
    imageFit: "object-cover",
    padding: undefined,
    hasOverlay: true,
    layout: "vertical",
});
</script>

<template>
    <component
        :is="as"
        :class="[
            'group relative w-full rounded-3xl border border-white/8 bg-black/50 text-left shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none',
            interactive
                ? 'hover:scale-[1.015] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)]'
                : '',
            layout === 'responsive'
                ? 'grid grid-cols-[auto_1fr] items-center gap-x-3.5 gap-y-1 p-3.5 sm:flex sm:flex-col sm:justify-between sm:gap-0 sm:p-5'
                : layout === 'horizontal'
                  ? 'grid grid-cols-[auto_1fr] items-center gap-x-3.5 gap-y-1 p-3.5'
                  : ['flex flex-col justify-between', padding || 'p-4 sm:p-5'],
        ]"
    >
        <div
            v-if="imageSrc || $slots.image"
            :class="[
                'relative overflow-hidden rounded-2xl bg-neutral-900 ring-1 ring-white/10',
                layout === 'responsive'
                    ? 'row-span-2 aspect-square h-20 w-20 shrink-0 min-[400px]:h-22 min-[400px]:w-22 sm:row-span-1 sm:aspect-square sm:h-auto sm:w-full'
                    : layout === 'horizontal'
                      ? 'row-span-2 aspect-square h-20 w-20 shrink-0 min-[400px]:h-22 min-[400px]:w-22'
                      : ['w-full', imageAspectRatio],
            ]"
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

        <header
            v-if="$slots.header"
            :class="[
                'min-w-0',
                layout === 'responsive'
                    ? 'mt-0 sm:mt-4 w-full'
                    : layout === 'horizontal'
                      ? 'mt-0'
                      : { 'mt-4': imageSrc || $slots.image },
            ]"
        >
            <slot name="header" />
        </header>

        <div
            v-if="$slots.default"
            :class="[
                'min-w-0',
                layout === 'responsive'
                    ? 'mt-1 sm:mt-3 sm:flex-1'
                    : layout === 'horizontal'
                      ? 'mt-1'
                      : {
                            'mt-3': $slots.header || imageSrc || $slots.image,
                            'flex-1': true,
                        },
            ]"
        >
            <slot />
        </div>

        <footer
            v-if="$slots.footer"
            :class="[
                'flex min-w-0 items-center justify-between',
                layout === 'responsive'
                    ? 'mt-1 sm:mt-4 sm:min-h-9 sm:pt-3 w-full'
                    : layout === 'horizontal'
                      ? 'mt-1'
                      : 'mt-4 min-h-9 pt-3',
            ]"
        >
            <slot name="footer" />
        </footer>
    </component>
</template>
