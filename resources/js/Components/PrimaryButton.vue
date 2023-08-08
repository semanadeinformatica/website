<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center px-4 py-2 bg-2023-teal border border-black font-semibold text-white relative hover:shadow-md shadow-2023-orange transition-shadow active:shadow-none">
        <slot />
    </button>
</template>
