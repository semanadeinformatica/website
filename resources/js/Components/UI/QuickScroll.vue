<script setup lang="ts">
import { ArrowUp, ArrowDown } from "@lucide/vue";
import { useScroll, type QuickScrollMode } from "@/Composables/useScroll";

const props = withDefaults(
    defineProps<{
        mode?: QuickScrollMode;
    }>(),
    { mode: "sections" },
);

const { isAtBottom, shouldShow, handleClick } = useScroll(props.mode);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2 scale-90"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-2 scale-90"
    >
        <button
            v-if="shouldShow"
            type="button"
            :aria-label="isAtBottom ? 'Voltar ao topo' : 'Próxima secção'"
            class="pill-container fixed right-6 bottom-6 z-40 h-11 w-11 cursor-pointer justify-center text-neutral-300 shadow-none transition-all duration-200 hover:scale-110 hover:border-white/25 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/20 active:scale-95 sm:right-8 sm:bottom-8 sm:h-12 sm:w-12"
            @click="handleClick"
        >
            <ArrowUp v-if="mode === 'top' || isAtBottom" :size="16" />
            <ArrowDown v-else :size="16" />
        </button>
    </Transition>
</template>
