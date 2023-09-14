<script setup lang="ts">

const borderColor: Record<string, string> = {
    orange: "border-2023-orange",
    "teal-dark": "border-2023-teal-dark",
    "red-dark": "border-2023-red-dark",
    red: "border-2023-red",
    teal: "border-2023-teal",
};

const textColor: Record<string, string> = {
    orange: "text-2023-orange",
    "teal-dark": "text-2023-teal-dark",
    "red-dark": "text-2023-red-dark",
    red: "text-2023-red",
    teal: "text-2023-teal",
};

const iconColor: Record<string, string> = {
    orange: "#f29325",
    "teal-dark": "#025259",
    "red-dark": "#b15d5d",
    red: "#d94f04",
    teal: "#007172",
};

defineProps<{
    color: string,
    person: unknown,
}>();
</script>

<template>
    <div class="flex flex-col items-center relative">
        <img :src="person.src" :alt="`${person.name}'s' image`" class="rounded-full border-4" :class="borderColor[color]">
        <div class="w-fit absolute bottom-2">
            <a :href="person.linkedin">
                <v-icon :fill="iconColor[color]" name="io-logo-linkedin" scale="1.4"></v-icon>
            </a>
            <a v-if="person.github" :href="person.github">
                <v-icon :fill="iconColor[color]" name="io-logo-github" scale="1.4"></v-icon>
            </a>
        </div>
    </div>
    <p class="font-bold" :class="textColor[color]">{{ person.name }}</p>
</template>

<style>
</style>