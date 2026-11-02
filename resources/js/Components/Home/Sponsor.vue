<script setup lang="ts">
import type { CompanyUser } from "@/Types/User";
import { ref } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import Card from "@/Components/UI/Card.vue";
import { ExternalLink } from "@lucide/vue";

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
        layout="centered"
        padding="p-4 sm:p-5"
        class="h-24 w-44 cursor-pointer focus:outline-none sm:h-28 sm:w-56"
        @click="options.modelValue = true"
    >
        <img
            :src="company?.profile_photo_url"
            :alt="company?.name"
            class="max-h-full max-w-[85%] object-contain opacity-85 brightness-95 transition-all duration-300 ease-out group-hover:scale-105 group-hover:opacity-100 group-hover:brightness-110"
        />
    </Card>

    <Modal v-model="options.modelValue" max-width="md">
        <div class="flex flex-col items-center text-center">
            <img
                :src="company?.profile_photo_url"
                :alt="company?.name"
                class="h-16 w-auto max-w-[200px] object-contain sm:h-20"
            />

            <div class="mt-4 flex w-full flex-col items-center">
                <h3 class="text-xl font-bold text-white sm:text-2xl">
                    {{ company?.name }}
                </h3>

                <div
                    v-if="company?.usertype?.description_html"
                    class="prose prose-invert prose-sm mt-5 max-h-60 w-full overflow-y-auto px-2 text-justify leading-relaxed text-neutral-300"
                    v-html="company?.usertype?.description_html"
                />

                <div
                    v-if="company?.usertype?.social_media?.website"
                    class="mt-6 flex w-full justify-center border-t border-white/10 pt-4"
                >
                    <a
                        :href="
                            company.usertype.social_media.website.startsWith(
                                'http',
                            )
                                ? company.usertype.social_media.website
                                : `https://${company.usertype.social_media.website}`
                        "
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 text-sm font-medium text-neutral-300 transition-colors hover:text-white"
                    >
                        <span>Visitar Website</span>
                        <ExternalLink :size="14" />
                    </a>
                </div>
            </div>
        </div>
    </Modal>
</template>
