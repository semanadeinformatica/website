<script setup lang="ts">
defineProps<{
    active: boolean;
    color: string;
    src: string;
    text: string;
}>();

const bgColor: Record<string, string> = {
    orange: "bg-2023-orange",
    "teal-dark": "bg-2023-teal-dark",
    "red-dark": "bg-2023-red-dark",
    red: "bg-2023-red",
    teal: "bg-2023-teal",
};
</script>

<template>
    <div
        class="sticker-clip-path group relative flex h-[220px] w-[220px] basis-0 justify-center overflow-hidden"
        :class="[active ? '' : 'grayscale', bgColor[color]]"
    >
        <img
            class="sticker-clip-path m-auto transition-all duration-500 group-hover:blur"
            src="https://picsum.photos/200/200"
            alt="Sticker"
        />
        <div
            class="sticker-clip-path absolute flex h-full w-full items-center justify-center bg-black bg-opacity-50 p-10 text-center text-white opacity-0 transition-all duration-500 group-hover:opacity-100"
        >
            {{ text }}
        </div>
    </div>
</template>

<style>
.sticker-clip-path {
    clip-path: url(#clip-path-svg);
    object-fit: cover;
}
</style>
