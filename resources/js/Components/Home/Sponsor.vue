<script setup lang="ts">
import { ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";

const options = ref({
    modelValue: false,
});

defineProps<{
    src: string;
    description: number;
    title: number;
    url: string;
    position?: string;
}>();
</script>

<template>
    <div class="ml-auto mr-auto cursor-pointer" :style="position">
        <img
            class="ml-auto mr-auto"
            :src="src"
            :alt="title + ''"
            @click="options.modelValue = true"
        />
        <VueFinalModal
            v-model="options.modelValue"
            class="flex items-center justify-center"
            content-class="max-w-xl mx-4 p-4 bg-2023-bg border border-black border-solid flex relative justify-center felx-col"
        >
            <img
                :src="src"
                class="absolute -top-20 w-48 border border-solid border-black shadow-xl shadow-2023-teal-dark"
            />
            <div
                class="flex w-full flex-col items-center gap-4 pt-20 text-2023-red"
            >
                <a
                    class="text-xl font-bold underline"
                    :href="url"
                    target="_blank"
                >
                    Title: {{ title }}
                    <v-icon
                        class="ml-1"
                        name="io-open"
                        fill="#d94f04"
                        scale="1.3"
                    ></v-icon>
                </a>
                <p class="text-justify">
                    {{ description }}
                    <br />
                    Lorem ipsum dolor sit amet consectetur adipisicing elit.
                    Natus adipisci voluptatem quod reprehenderit commodi nostrum
                    ipsum eius eligendi qui vitae rerum dicta iure, officiis
                    reiciendis totam veniam animi quidem laborum.
                </p>
            </div>
        </VueFinalModal>
    </div>
</template>
