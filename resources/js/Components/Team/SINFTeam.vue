<script setup lang="ts">
import TeamMember from './TeamMember.vue';

const shadowColor: Record<string, string> = {
    orange: "shadow-2023-orange",
    "teal-dark": "shadow-2023-teal-dark",
    "red-dark": "shadow-2023-red-dark",
    red: "shadow-2023-red",
    teal: "shadow-2023-teal",
};

const textColor: Record<string, string> = {
    orange: "text-2023-orange",
    "teal-dark": "text-2023-teal-dark",
    "red-dark": "text-2023-red-dark",
    red: "text-2023-red",
    teal: "text-2023-teal",
};

defineProps<{
    title: string,
    color: string,
    people: unknown
}>();

</script>

<template>
    <section class="flex flex-col items-center gap-4">
        <p class="w-fit text-xl shadow border border-black p-2 px-6 font-bold text-center" :class="textColor[color] || shadowColor[color]">{{ title }}</p>
        <section class="flex flex-row gap-4 flex-wrap justify-center">
            <div v-for="person in people" :key="person" class="flex flex-col items-center">
                <TeamMember :person="person" :color="color"></TeamMember>
            </div>
        </section>
    </section>
</template>

<style>
</style>