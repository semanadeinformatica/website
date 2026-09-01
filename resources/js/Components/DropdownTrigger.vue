<script setup lang="ts">
interface Props {
    active?: boolean;
}
defineProps<Props>();
</script>

<template>
    <button
        type="button"
        class="inline-flex cursor-pointer items-center gap-1 rounded-full px-3 py-1.5 text-[13px] font-medium text-neutral-300 transition-all duration-150 hover:bg-white/8 hover:text-white focus:outline-none"
        :class="{ 'bg-white/8 text-white': active }"
    >
        <slot />
    </button>
</template>
