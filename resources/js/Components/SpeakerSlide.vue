<script setup>

import { watch } from 'vue';


const props = defineProps({
    slide_id: Object,
    currentSlide: Object
});

let active;

watch(() => props.currentSlide, (newValue, oldValue) => {
    active = newValue.activeSpeaker == props.slide_id.slide;
});

</script>


<template>
    <div>
        <img src="https://picsum.photos/200" class=" border-2 border-solid border-black rounded-full w-[200px] ease-in-out transition-all duration-500 object-cover" :class="{[`border-2023-red w-[400px]`]:active}" alt="">
        <p class="py-4 text-2023-teal-dark">John Doe {{slide_id.slide}}</p>
    </div>
</template>

