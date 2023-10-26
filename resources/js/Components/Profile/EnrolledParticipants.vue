<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { usePage } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";

type Visitor = Participant & {
    can_see_cv: boolean;
    can_see_linkedin: boolean;
};

const visitors = usePage().props.participants as Visitor[];
</script>

<template>
    <div
        v-if="visitors.length > 0"
        class="mt-10 flex flex-col items-center border border-black bg-2023-bg shadow-lg shadow-2023-teal"
    >
        <div
            v-for="visitor in visitors"
            :key="visitor.id"
            class="flex w-full items-center justify-between p-3 even:bg-2023-orange even:bg-opacity-20"
        >
            {{ visitor.user?.name ?? visitor.id }}
            <div class="flex gap-4">
                <a
                    v-if="
                        visitor.can_see_linkedin &&
                        visitor.social_media?.linkedin
                    "
                    :href="visitor.social_media.linkedin"
                    target="_blank"
                    title="Linkedin"
                >
                    <OhVueIcon name="io-logo-linkedin" scale="1.3" />
                </a>

                <a
                    v-if="visitor.can_see_cv && visitor.cv_url"
                    :href="visitor.cv_url"
                    target="_blank"
                    title="CV"
                >
                    <OhVueIcon name="io-document-text" scale="1.3" />
                </a>
            </div>
        </div>
    </div>
    <div v-else class="flex h-full flex-1 items-center justify-center">
        <p class="text-center text-2xl font-bold text-2023-teal">
            Ainda nenhum participante visitou a sua empresa.
        </p>
    </div>
</template>
