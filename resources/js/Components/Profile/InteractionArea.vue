<script setup lang="ts">
import {
    ref,
    watch,
    type UnwrapRef,
    onMounted,
    computed,
    type VNode,
} from "vue";

const selected = ref<HTMLElement | null>(null);

// Change these values with the values of the buttons
const selectedType = ref<"ticket" | "sticker" | "visitHistory">("ticket");
type Props = {
    buttons: {
        ticket: { id: string; title: string; component: VNode };
        sticker: { id: string; title: string; component: VNode };
        visitHistory: { id: string; title: string; component: VNode };
    };
};

const { buttons } = defineProps<Props>();

watch(selected, (newValue, oldValue) => {
    oldValue?.classList.toggle("selected");
    newValue?.classList.toggle("selected");
    selectedType.value =
        (newValue?.dataset.type as UnwrapRef<typeof selectedType>) ?? "ticket";
});

const view = computed(() => {
    return buttons[selectedType.value].component;
});

const toggle = ({ target }: MouseEvent) => {
    selected.value = target as HTMLElement;
};

onMounted(() => {
    selected.value = document.querySelector(
        "#tab-picker > button:first-of-type",
    ) as HTMLElement;
    selectedType.value =
        (selected.value.dataset.type as UnwrapRef<typeof selectedType>) ??
        "ticket";
});
</script>

<template>
    <div
        id="tab-picker"
        class="flex flex-row justify-center gap-4 pt-5 font-bold text-2023-teal"
    >
        <button
            v-for="button in buttons"
            :key="button.id"
            class="transition"
            :data-type="button.id"
            @click="toggle"
        >
            {{ button.title }}
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
