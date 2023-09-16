<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';

interface Props {
    day: {
        theme: string;
    };
}

const selected = ref<HTMLElement | null>(null);

const toggle = ({target}: MouseEvent) => {
    selected.value = target as HTMLElement;
}

watch(selected, (newValue, oldValue) => {
    oldValue?.classList.toggle("selected");
    newValue?.classList.toggle("selected");
});

onMounted(() => {
    selected.value = document.querySelector("ul.tab-picker > li:first-of-type") as HTMLElement;    
});

defineProps<Props>();
</script>

<template>
    <section class="pt-8">
        <ul class="flex flex-row gap-4 justify-center text-2023-teal-dark font-bold tab-picker">
            <li class="transition" @click="toggle">Palestras</li>
            <li class="transition" @click="toggle">Atividades</li>
            <li class="transition" @click="toggle">Bancas</li>
        </ul>
    </section>
</template>

<style scoped>
.selected {
    color: rgb(242, 147, 37);
    text-decoration: underline;
}
</style>