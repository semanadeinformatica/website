<script setup lang="ts">
import type { CompanyUser } from "@/Types/User";
import { ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";

const options = ref({
    modelValue: false,
});

interface Props {
    company?: CompanyUser;
}

defineProps<Props>();
</script>

<template>
    <img
        v-bind="$attrs"
        class="col-span-2 h-auto max-h-[200px] w-full cursor-pointer object-contain"
        :src="company?.profile_photo_url"
        :alt="company?.name"
        @click="options.modelValue = true"
    />
    <VueFinalModal
        v-model="options.modelValue"
        class="flex items-center justify-center"
        content-class="max-w-xl mx-4 p-4 bg-2023-bg border border-black border-solid flex relative justify-center items-center flex-col"
    >
        <img
            :src="company?.profile_photo_url"
            class="absolute -top-20 h-40 w-auto border border-solid border-black shadow-xl justify-center mr-2 shadow-2023-teal-dark"
        />
        <div
            class="flex w-full flex-col items-center gap-4 pt-20 text-2023-red"
        >
            <a class="text-xl font-bold underline" href="#" target="_blank">
                {{ company?.name }}
                <v-icon class="ml-1" name="io-open" fill="#d94f04"></v-icon>
            </a>
            <p class="text-justify">{{ company?.usertype?.description }}</p>
        </div>
    </VueFinalModal>
</template>
