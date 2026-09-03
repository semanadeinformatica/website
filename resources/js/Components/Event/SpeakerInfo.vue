<script setup lang="ts">
import { computed, ref } from "vue";
import type { SpeakerUser } from "@/Types/User";
import Card from "@/Components/UI/Card.vue";
import SocialIcon from "@/Components/UI/SocialIcon.vue";

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
    <Card
        as="article"
        padding="p-6 sm:p-8"
        class="md:flex-row md:items-start md:gap-8"
    >
        <div class="flex shrink-0 flex-col items-center gap-4">
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
                    <SocialIcon :platform="item.platform" :size="16" />
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
                    <span v-if="user.usertype?.title">{{
                        user.usertype.title
                    }}</span>
                    <span
                        v-if="
                            user.usertype?.title && user.usertype?.organization
                        "
                    >
                        ·
                    </span>
                    <span
                        v-if="user.usertype?.organization"
                        class="text-neutral-300"
                        >{{ user.usertype.organization }}</span
                    >
                </p>
            </div>

            <div
                v-if="user.usertype?.description_html"
                class="prose prose-invert mt-4 max-w-none text-justify text-xs leading-relaxed text-neutral-300 sm:text-sm"
                v-html="user.usertype?.description_html"
            />
        </div>
    </Card>
</template>
