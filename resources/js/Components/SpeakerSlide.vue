<script setup>
import { watch } from "vue";

const props = defineProps({
    slide_id: Object,
    currentSlide: Object,
});

let active;

watch(
    () => props.currentSlide,
    (newValue, oldValue) => {
        active = newValue.activeSpeaker == props.slide_id.slide;
    },
);
</script>

<template>
    <div>
        <img
            src="https://picsum.photos/200"
            class="w-[200px] rounded-full border-2 border-solid border-black object-cover transition-all duration-500 ease-in-out"
            :class="{ [`w-[400px] border-2023-red`]: active }"
            alt=""
        />
        <p class="py-4 text-2023-teal-dark">John Doe {{ slide_id.slide }}</p>
    </div>
</template>
