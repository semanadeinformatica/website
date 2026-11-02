<script setup lang="ts">
interface Props {
    text?: string;
    class?: string;
}

withDefaults(defineProps<Props>(), {
    text: "Em breve",
    class: "",
});
</script>

<template>
    <div
        :class="[
            'flex items-center justify-center py-16 text-center text-sm font-medium tracking-wide text-neutral-400 select-none sm:text-base',
            $props.class,
        ]"
    >
        <span>{{ text }}</span>
    </div>
</template>
