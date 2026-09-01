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
    <button
        type="button"
        class="group flex h-28 w-48 items-center justify-center rounded-3xl border border-white/8 bg-black/50 p-5 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.015] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] focus:outline-none sm:h-32 sm:w-56 sm:p-6"
        @click="options.modelValue = true"
    >
        <img
            :src="company?.profile_photo_url"
            :alt="company?.name"
            class="max-h-14 max-w-[85%] object-contain opacity-85 brightness-95 transition-all duration-300 ease-out group-hover:scale-105 group-hover:opacity-100 group-hover:brightness-110 sm:max-h-16"
        />
    </button>

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
            <svg
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
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
                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                        />
                    </svg>
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
