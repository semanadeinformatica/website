<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import Ticket from "./Ticket.vue";
const tickets = computed(() => {
    return usePage().props.tickets;
});
</script>

<template>
    <div
        class="grid items-center justify-center gap-10 self-center pt-8"
        style="grid-template-columns: repeat(auto-fill, 350px)"
    >
        <div v-for="item in tickets" :key="item" class="max-w-sm">
            <Ticket state="acquired" :event="item"></Ticket>
        </div>
    </div>
</template>
