<script setup lang="ts">
import { watch } from "vue";

const props = defineProps<{
    slideID: number;
    currentSlide: number;
}>();

let active: boolean = props.slideID == props.currentSlide;

watch(
    () => props.currentSlide,
    (newValue, oldValue) => {
        active = newValue == props.slideID;
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
            John Doe {{ slideID }}
        </p>
    </div>
</template>
