<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
    slideID: number;
    currentSlide: number;
}>();

const active = computed(() => {
    return props.currentSlide == props.slideID;
});
</script>

<template>
    <div>
        <img
            src="https://picsum.photos/200"
            class="w-[200px] rounded-full border-2 border-solid border-black object-cover"
            :class="{ [`w-[400px]`]: active }"
            alt=""
        />
        <p v-if="active" class="py-4 text-lg text-2023-teal-dark">
            John Doe {{ slideID }}
        </p>
    </div>
</template>
