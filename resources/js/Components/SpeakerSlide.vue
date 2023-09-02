<script setup lang="ts">
import { watch } from "vue";

const props = defineProps({
    slideID: Object,
    currentSlide: Object,
});

let active: boolean;

watch(
    () => props.currentSlide,
    (newValue, oldValue) => {
        active = newValue?.activeSpeaker == props.slideID?.slide;
    },
);
</script>

<template>
    <div>
        <img
            src="https://picsum.photos/200"
            class="w-[200px] rounded-full border-2 border-solid border-black object-cover transition-all duration-500 ease-in-out"
            :class="{ [`w-[400px]`]: active }"
            alt=""
        />
        <p v-if="active" class="py-4 text-2023-teal-dark">
            John Doe {{ slideID?.slide }}
        </p>
    </div>
</template>
