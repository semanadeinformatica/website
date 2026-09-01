<script setup lang="ts">
import { computed, ref } from "vue";
import type { SpeakerUser } from "@/Types/User";

interface Props {
    user: SpeakerUser;
}

const props = defineProps<Props>();
const user = computed(() => props.user);
const imageError = ref(false);

const rawSocialMedia = computed<Record<string, unknown>>(() => {
    return (user.value.usertype?.social_media ?? {}) as Record<string, unknown>;
});

const formatSocialUrl = (platform: string, val: string): string => {
    const trimmed = val.trim();
    if (!trimmed) return "";
    if (platform === "email") {
        return trimmed.startsWith("mailto:") ? trimmed : `mailto:${trimmed}`;
    }
    if (trimmed.startsWith("http://") || trimmed.startsWith("https://")) {
        return trimmed;
    }
    if (platform === "github") {
        return `https://github.com/${trimmed.replace(/^@/, "")}`;
    }
    if (platform === "linkedin") {
        return `https://linkedin.com/in/${trimmed.replace(/^@/, "")}`;
    }
    if (platform === "instagram") {
        return `https://instagram.com/${trimmed.replace(/^@/, "")}`;
    }
    if (platform === "twitter") {
        return `https://x.com/${trimmed.replace(/^@/, "")}`;
    }
    return `https://${trimmed}`;
};

const socialLinks = computed(() => {
    const supported = [
        "github",
        "linkedin",
        "website",
        "instagram",
        "twitter",
        "email",
    ] as const;
    const entries: {
        platform: (typeof supported)[number];
        url: string;
        label: string;
    }[] = [];

    for (const key of supported) {
        const val = rawSocialMedia.value[key];
        if (typeof val === "string" && val.trim().length > 0) {
            entries.push({
                platform: key,
                url: formatSocialUrl(key, val),
                label:
                    key === "email"
                        ? "Email"
                        : key === "website"
                          ? "Website"
                          : key.charAt(0).toUpperCase() + key.slice(1),
            });
        }
    }

    return entries;
});
</script>

<template>
    <article
        class="group relative flex flex-col gap-6 rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-8 md:flex-row md:items-start md:gap-8"
    >
        <div class="flex flex-col items-center gap-4 shrink-0">
            <div
                class="relative h-32 w-32 overflow-hidden rounded-2xl bg-neutral-900 ring-1 ring-white/10 sm:h-40 sm:w-40"
            >
                <img
                    v-if="user.profile_photo_url && !imageError"
                    :src="user.profile_photo_url"
                    :alt="user.usertype?.display_name ?? user.name"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    @error="imageError = true"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-neutral-900 text-2xl font-bold text-neutral-400 sm:text-3xl"
                >
                    {{ (user.usertype?.display_name ?? user.name).charAt(0) }}
                </div>
            </div>

            <div
                v-if="socialLinks.length > 0"
                class="flex flex-wrap items-center justify-center gap-1.5"
            >
                <a
                    v-for="item in socialLinks"
                    :key="item.platform"
                    :href="item.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex h-7 w-7 items-center justify-center rounded-full text-neutral-400 transition-all hover:bg-white/10 hover:text-white"
                    :aria-label="item.label"
                >
                    <svg
                        v-if="item.platform === 'github'"
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

                    <svg
                        v-else-if="item.platform === 'linkedin'"
                        class="h-4 w-4"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"
                        />
                    </svg>

                    <svg
                        v-else-if="item.platform === 'website'"
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

                    <svg
                        v-else-if="item.platform === 'instagram'"
                        class="h-4 w-4"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                        />
                    </svg>

                    <svg
                        v-else-if="item.platform === 'twitter'"
                        class="h-4 w-4"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                        />
                    </svg>

                    <svg
                        v-else-if="item.platform === 'email'"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>
                </a>
            </div>
        </div>

        <div class="flex min-w-0 flex-1 flex-col text-center md:text-left">
            <div class="space-y-1">
                <h3
                    class="text-xl font-bold tracking-tight text-white sm:text-2xl"
                >
                    {{ user.usertype?.display_name ?? user.name }}
                </h3>
                <p
                    v-if="user.usertype?.title || user.usertype?.organization"
                    class="text-xs font-medium text-neutral-400 sm:text-sm"
                >
                    <span v-if="user.usertype?.title">{{ user.usertype.title }}</span>
                    <span v-if="user.usertype?.title && user.usertype?.organization"> · </span>
                    <span v-if="user.usertype?.organization" class="text-neutral-300">{{ user.usertype.organization }}</span>
                </p>
            </div>

            <div
                v-if="user.usertype?.description_html"
                class="prose prose-invert max-w-none text-justify text-xs leading-relaxed text-neutral-300 sm:text-sm mt-4"
                v-html="user.usertype?.description_html"
            />
        </div>
    </article>
</template>
