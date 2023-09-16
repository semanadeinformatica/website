<script setup lang="ts">
import Info from "@/Components/Program/Day/Info.vue";
import NoInfo from "@/Components/Program/Day/NoInfo.vue";

interface Props {
    day: {
        theme: string;
    } | null;
}

const { day } = defineProps<Props>();
</script>

<template>
    <Info v-if="day !== null" :day="day" />
    <NoInfo v-else />
</template>
