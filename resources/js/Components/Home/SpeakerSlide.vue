<script setup lang="ts">
import type { SpeakerUser } from "@/Types/User";
import { computed } from "vue";

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
    <div
        class="group relative flex h-full w-full flex-col justify-between rounded-3xl border border-white/8 bg-black/50 p-4 text-left shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.015] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-5"
    >
        <div
            class="relative aspect-square w-full overflow-hidden rounded-2xl bg-neutral-900 ring-1 ring-white/10"
        >
            <img
                :src="speaker.profile_photo_url"
                :alt="displayName"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            <div
                class="absolute inset-0 bg-linear-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
            />
        </div>

        <div class="mt-4 flex flex-1 flex-col justify-between">
            <div>
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
            </div>

            <div class="mt-4 flex min-h-9 items-center justify-between pt-3">
                <div class="flex items-center gap-2">
                    <a
                        v-if="socialMedia.github"
                        :href="socialMedia.github"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex h-7 w-7 items-center justify-center rounded-full text-neutral-400 transition-all hover:bg-white/10 hover:text-white"
                        aria-label="GitHub"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                            />
                        </svg>
                    </a>

                    <a
                        v-if="socialMedia.linkedin"
                        :href="socialMedia.linkedin"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex h-7 w-7 items-center justify-center rounded-full text-neutral-400 transition-all hover:bg-white/10 hover:text-white"
                        aria-label="LinkedIn"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"
                            />
                        </svg>
                    </a>

                    <a
                        v-if="socialMedia.website"
                        :href="socialMedia.website"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex h-7 w-7 items-center justify-center rounded-full text-neutral-400 transition-all hover:bg-white/10 hover:text-white"
                        aria-label="Website"
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
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                            />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
