<script setup lang="ts">
import Ticket from "./Ticket.vue";
defineProps<{ items: any }>();
</script>

<template>
    <div
        class="grid items-center justify-center gap-10 self-center pt-8"
        style="grid-template-columns: repeat(auto-fill, 380px)"
    >
        <div v-for="item in items.items" :key="item" class="max-w-sm">
            <Ticket :state="item"></Ticket>
        </div>
    </div>
</template>
