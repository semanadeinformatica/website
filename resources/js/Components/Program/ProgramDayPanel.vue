<script setup lang="ts">
import Info from "@/Components/Program/Day/Info.vue";
import NoInfo from "@/Components/Program/Day/NoInfo.vue";
import type EventDay from "@/Types/EventDay";

interface Props {
    day: EventDay | null;
}

const { day } = defineProps<Props>();
</script>

<template>
    <Info v-if="day !== null" :day="day" />
    <NoInfo v-else />
</template>
