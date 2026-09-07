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

const displayName = computed(() => {
    return user.value.usertype?.display_name ?? user.value.name;
});
</script>

<template>
    <Card
        as="article"
        layout="horizontal"
        :image-src="
            user.profile_photo_url && !imageError
                ? user.profile_photo_url
                : undefined
        "
        :image-alt="displayName"
        padding="p-6 sm:p-7"
    >
        <template v-if="!user.profile_photo_url || imageError" #image>
            <div
                class="flex h-full w-full items-center justify-center bg-neutral-900 text-2xl font-bold text-neutral-400 sm:text-3xl"
            >
                {{ displayName.charAt(0) }}
            </div>
        </template>

        <template #header>
            <h3 class="text-xl font-bold tracking-tight text-white sm:text-2xl">
                {{ displayName }}
            </h3>

            <div
                v-if="user.usertype?.title || user.usertype?.organization"
                class="mt-1 flex flex-wrap items-center justify-center gap-x-2 gap-y-0.5 text-xs font-medium text-neutral-400 sm:justify-start sm:text-sm"
            >
                <span v-if="user.usertype?.title" class="text-neutral-300">
                    {{ user.usertype.title }}
                </span>
                <span
                    v-if="user.usertype?.title && user.usertype?.organization"
                    class="text-neutral-600"
                >
                    ·
                </span>
                <span
                    v-if="user.usertype?.organization"
                    class="text-neutral-400"
                >
                    {{ user.usertype.organization }}
                </span>
            </div>
        </template>

        <template
            v-if="user.usertype?.description_html || user.usertype?.description"
            #default
        >
            <div
                v-if="user.usertype?.description_html"
                class="prose prose-invert prose-p:leading-relaxed prose-sm max-w-none text-left text-xs leading-relaxed text-neutral-300 sm:text-sm"
                v-html="user.usertype?.description_html"
            />
            <p
                v-else-if="user.usertype?.description"
                class="text-left text-xs leading-relaxed text-neutral-300 sm:text-sm"
            >
                {{ user.usertype.description }}
            </p>
        </template>

        <template v-if="socialLinks.length > 0" #footer>
            <div
                class="flex flex-wrap items-center justify-center gap-1.5 sm:justify-start"
            >
                <a
                    v-for="item in socialLinks"
                    :key="item.platform"
                    :href="item.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex h-7 w-7 items-center justify-center rounded-xl bg-white/4 text-neutral-400 ring-1 ring-white/6 transition-all duration-200 hover:bg-white/10 hover:text-white hover:ring-white/15"
                    :aria-label="item.label"
                >
                    <SocialIcon :platform="item.platform" :size="15" />
                </a>
            </div>
        </template>
    </Card>
</template>
