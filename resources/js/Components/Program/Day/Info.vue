<script setup lang="ts">
import type EventDay from "@/Types/EventDay";
import { ref, onMounted, watch } from "vue";

interface Props {
    day: EventDay;
}

const selected = ref<HTMLElement | null>(null);

const toggle = ({ target }: MouseEvent) => {
    selected.value = target as HTMLElement;
};

watch(selected, (newValue, oldValue) => {
    oldValue?.classList.toggle("selected");
    newValue?.classList.toggle("selected");
});

onMounted(() => {
    selected.value = document.querySelector(
        "ul.tab-picker > li:first-of-type",
    ) as HTMLElement;
});

defineProps<Props>();
</script>

<template>
    <section class="pt-8">
        <ul
            class="tab-picker flex flex-row justify-center gap-4 font-bold text-2023-teal-dark"
        >
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
