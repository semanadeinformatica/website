<script setup lang="ts">
interface Props {
    on?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div>
        <transition
            leave-active-class="transition ease-in duration-1000"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="on" class="text-sm text-2023-teal">
                <slot />
            </div>
        </transition>
    </div>
</template>
