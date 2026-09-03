<script setup lang="ts">
import type { CompanyUser } from "@/Types/User";
import { ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";

import Card from "@/Components/UI/Card.vue";
import { X, ExternalLink } from "@lucide/vue";

const options = ref({
    modelValue: false,
});

interface Props {
    company?: CompanyUser;
}

defineProps<Props>();
</script>

<template>
    <Card
        as="button"
        padding="p-5 sm:p-6"
        class="h-28 w-48 cursor-pointer items-center justify-center focus:outline-none sm:h-32 sm:w-56"
        @click="options.modelValue = true"
    >
        <img
            :src="company?.profile_photo_url"
            :alt="company?.name"
            class="max-h-14 max-w-[85%] object-contain opacity-85 brightness-95 transition-all duration-300 ease-out group-hover:scale-105 group-hover:opacity-100 group-hover:brightness-110 sm:max-h-16"
        />
    </Card>

    <VueFinalModal
        v-model="options.modelValue"
        class="z-50 flex items-center justify-center p-4"
        overlay-class="bg-black/75 backdrop-blur-sm"
        content-class="
            relative w-full max-w-lg rounded-3xl border border-white/10
            bg-[#111215] p-6 sm:p-8 text-white backdrop-blur-2xl shadow-none
            flex flex-col items-center gap-6
        "
    >
        <button
            type="button"
            class="pill-container absolute top-4 right-4 h-8 w-8 justify-center text-neutral-400 transition-colors hover:text-white"
            aria-label="Close modal"
            @click="options.modelValue = false"
        >
            <X :size="16" />
        </button>

        <img
            :src="company?.profile_photo_url"
            :alt="company?.name"
            class="mt-2 h-16 max-w-[70%] object-contain"
        />

        <div class="flex w-full flex-col items-center gap-4 text-center">
            <div class="flex flex-wrap items-center justify-center gap-3">
                <h3 class="text-xl font-bold text-white">
                    {{ company?.name }}
                </h3>
                <a
                    v-if="company?.usertype?.social_media?.website"
                    :href="company.usertype.social_media.website"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="pill-container pill-item gap-1.5 px-3 py-1 text-xs font-medium text-neutral-300 hover:text-white"
                >
                    <span>Website</span>
                    <ExternalLink :size="14" />
                </a>
            </div>

            <div
                v-if="company?.usertype?.description_html"
                class="prose prose-invert prose-sm max-h-72 w-full overflow-y-auto px-2 text-justify leading-relaxed text-neutral-300"
                v-html="company?.usertype?.description_html"
            />
        </div>
    </VueFinalModal>
</template>
