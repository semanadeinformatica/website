<script setup lang="ts">
import type { SpeakerUser } from "@/Types/User";
import { computed } from "vue";
import Card from "@/Components/UI/Card.vue";
import SocialIcon from "@/Components/UI/SocialIcon.vue";

interface Props {
    speaker: SpeakerUser;
}
const props = defineProps<Props>();

const speaker = computed(() => props.speaker);

const socialMedia = computed(() => {
    return Object.fromEntries(
        Object.entries(speaker.value.usertype?.social_media ?? {}).filter(
            ([key, value]) =>
                ["github", "linkedin", "website"].includes(key) &&
                value != null &&
                value !== "",
        ),
    ) as Record<"github" | "linkedin" | "website", string>;
});

const displayName = computed(() => {
    if (speaker.value.usertype?.display_name) {
        return speaker.value.usertype.display_name;
    }
    return speaker.value.name;
});
</script>

<template>
    <Card
        :image-src="speaker.profile_photo_url"
        :image-alt="displayName"
        class="h-full w-full"
    >
        <template #header>
            <h4
                class="truncate text-base font-bold tracking-tight text-white transition-colors group-hover:text-neutral-200"
            >
                {{ displayName }}
            </h4>
            <p
                class="mt-1 line-clamp-2 min-h-9 text-xs leading-relaxed font-light text-neutral-400"
            >
                <span v-if="speaker.usertype?.title">
                    {{ speaker.usertype.title }}
                </span>
                <span
                    v-if="
                        speaker.usertype?.title &&
                        speaker.usertype?.organization
                    "
                    class="mx-1 text-neutral-600"
                    >•</span
                >
                <span
                    v-if="speaker.usertype?.organization"
                    class="font-medium text-neutral-300"
                >
                    {{ speaker.usertype.organization }}
                </span>
            </p>
        </template>

        <template v-if="Object.keys(socialMedia).length > 0" #footer>
            <div class="flex items-center gap-2">
                <a
                    v-for="(url, platform) in socialMedia"
                    :key="platform"
                    :href="url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex h-7 w-7 items-center justify-center rounded-full text-neutral-400 transition-all hover:bg-white/10 hover:text-white"
                    :aria-label="platform"
                >
                    <SocialIcon :platform="platform" :size="16" />
                </a>
            </div>
        </template>
    </Card>
</template>
