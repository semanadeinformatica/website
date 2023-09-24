<script setup lang="ts">
import { ref, watch, type UnwrapRef, onMounted, computed, h } from "vue";
import StickerWrapper from "./StickerWrapper.vue";
import TicketWrapper from "./TicketWrapper.vue";

const selected = ref<HTMLElement | null>(null);
const selectedType = ref<"ticket" | "sticker">("ticket");

watch(selected, (newValue, oldValue) => {
    oldValue?.classList.toggle("selected");
    newValue?.classList.toggle("selected");
    selectedType.value =
        (newValue?.dataset.type as UnwrapRef<typeof selectedType>) ?? "ticket";
});

const view = computed(() => {
    switch (selectedType.value) {
        case "ticket":
            return {
                id: h(TicketWrapper),
                items: [
                    "acquired",
                    "acquired",
                    "available",
                    "available",
                    "used",
                ],
            };
        case "sticker":
            return {
                id: h(StickerWrapper),
                items: [
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: true,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                    {
                        active: false,
                        color: "red",
                        src: "",
                        text: "hellowold",
                    },
                ],
            };
        default:
            return [];
    }
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

defineProps<{
    buttons: {
        id: string;
        title: string;
        component: string;
    }[];
}>();
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
        <component :is="view.id" :items="view"></component>
    </KeepAlive>
</template>

<style scoped>
.selected {
    color: rgb(242, 147, 37);
    text-decoration: underline;
}
</style>
