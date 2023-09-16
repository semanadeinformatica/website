<script setup lang="ts">
import type Speaker from "@/Types/Speaker";

interface Props {
    speaker: Speaker;
    reverse?: number;
}

defineProps<Props>();
</script>

<template>
    <div
        class="mx-9 flex w-fit flex-row flex-wrap gap-16 pt-10"
        :class="reverse ? 'flex-row-reverse self-end' : ''"
    >
        <img
            class="border-2 border-black shadow-lg shadow-2023-red"
            src="https://picsum.photos/300/300"
            alt=""
        />
        <div class="flex flex-col justify-center gap-10 text-2023-teal-dark">
            <div
                class="flex w-fit flex-row justify-center gap-6"
                :class="reverse ? 'flex-row-reverse self-end' : ''"
            >
                <div>
                    <h2 class="text-3xl font-bold uppercase">
                        {{ speaker.name }}
                    </h2>
                    <span class="w-fit text-lg"
                        >{{ speaker.title }} @{{ speaker.organization }}</span
                    >
                </div>

                <div class="flex items-center gap-1">
                    <v-icon name="io-globe-outline"></v-icon>
                </div>
            </div>
            <p class="col-span-2 row-start-2 max-w-xl text-justify">
                {{ speaker.description }}
            </p>
        </div>
    </div>
</template>

<style></style>
