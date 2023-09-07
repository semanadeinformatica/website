<script setup lang="ts">
import { ref } from "vue";
import { VueFinalModal, ModalsContainer } from "vue-final-modal";
import "vue-final-modal/style.css";

const getInitialValues = () => ({
    modelValue: false,
});

const options = ref(getInitialValues());

const props = defineProps<{
    src: string;
    description: number;
    title: number;
    url: string;
    position?: string;
}>();
</script>

<template>
    <div class="ml-auto mr-auto" :class="props.position">
        <img
            class="ml-auto mr-auto"
            :src="props.src"
            :alt="props.title + ''"
            @click="options.modelValue = true"
        />
        <VueFinalModal
            v-model="options.modelValue"
            class="flex items-center justify-center"
            content-class="max-w-xl mx-4 p-4 bg-2023-bg border border-black border-solid flex relative justify-center felx-col"
        >
            <img
                :src="props.src"
                class="absolute -top-20 w-[200px] border border-solid border-black shadow-xl shadow-2023-teal-dark"
            />
            <div
                class="flex w-full flex-col items-center gap-4 pt-20 text-2023-red"
            >
                <a
                    class="text-xl font-bold underline"
                    :href="props.url"
                    target="_blank"
                >
                    Title: {{ props.title }}
                    <v-icon
                        class="ml-1"
                        name="io-open"
                        fill="#d94f04"
                        scale="1.3"
                    ></v-icon>
                </a>
                <p class="text-justify">
                    {{ props.description }}
                    <br />
                    Lorem ipsum dolor sit amet consectetur adipisicing elit.
                    Natus adipisci voluptatem quod reprehenderit commodi nostrum
                    ipsum eius eligendi qui vitae rerum dicta iure, officiis
                    reiciendis totam veniam animi quidem laborum.
                </p>
            </div>
        </VueFinalModal>
        <ModalsContainer />
    </div>
</template>

<style>
.vfm__overlay {
    background-color: rgba(248, 245, 231, 0.5);
}
</style>
