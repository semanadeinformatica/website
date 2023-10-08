<script setup lang="ts">
import { ref, watch, type UnwrapRef, onMounted, computed } from "vue";
import { type Tabs } from "@/Types/ProfilePage";

type Props = {
    buttons: Tabs;
};

const { buttons } = defineProps<Props>();

const selected = ref<HTMLElement | null>(null);

const firstType = Object.keys(buttons)[0];

const selectedType = ref<keyof typeof buttons>(firstType);

watch(selected, (newValue, oldValue) => {
    oldValue?.classList.toggle("selected");
    newValue?.classList.toggle("selected");

    selectedType.value =
        (newValue?.dataset.type as UnwrapRef<typeof selectedType>) ?? firstType;
});

const view = computed(() => {
    return buttons[selectedType.value]?.component;
});

const toggle = ({ target }: MouseEvent) => {
    selected.value = target as HTMLElement;
};

onMounted(() => {
    selected.value = document.querySelector<HTMLElement>(
        "#tab-picker > button:first-of-type",
    );

    if (selected.value)
        selectedType.value =
            (selected.value.dataset.type as UnwrapRef<typeof selectedType>) ??
            firstType;
});
</script>

<template>
    <div
        id="tab-picker"
        class="flex flex-row justify-center gap-4 pt-5 font-bold text-2023-teal"
    >
        <button
            v-for="(button, id) in buttons"
            :key="id"
            class="transition"
            :data-type="id"
            @click="toggle"
        >
            {{ button.label }}
        </button>
    </div>
    <KeepAlive>
        <component :is="view"></component>
    </KeepAlive>
</template>

<style scoped>
.selected {
    color: rgb(242, 147, 37);
    text-decoration: underline;
}
</style>
