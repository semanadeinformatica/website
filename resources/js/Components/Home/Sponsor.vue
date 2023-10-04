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
        class="col-span-2 w-full cursor-pointer object-contain"
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
            class="absolute -top-20 mr-2 h-40 w-auto justify-center border border-solid border-black shadow-xl shadow-2023-teal-dark"
        />
        <div
            class="flex w-full flex-col items-center gap-4 pt-20 text-2023-red"
        >
            <a
                v-if="company?.usertype?.social_media?.website"
                class="text-xl font-bold underline"
                :href="company.usertype.social_media.website"
                target="_blank"
            >
                {{ company.name }}
                <v-icon class="ml-1" name="io-open" fill="#d94f04"></v-icon>
            </a>
            <span v-else class="text-xl font-bold underline">
                {{ company?.name }}
            </span>
            <p class="text-justify">{{ company?.usertype?.description }}</p>
        </div>
    </VueFinalModal>
</template>
