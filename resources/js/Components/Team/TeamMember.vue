<script setup lang="ts">
import { computed, ref } from "vue";
import type Staff from "@/Types/Staff";
import Card from "@/Components/UI/Card.vue";
import SocialIcon from "@/Components/UI/SocialIcon.vue";
import { Users } from "@lucide/vue";

interface Props {
    staff: Staff;
}

const props = defineProps<Props>();

const imageError = ref(false);

const staff = computed(() => props.staff);
const user = computed(() => staff.value.participant?.user);
const name = computed(() => user.value?.name ?? "Membro da Equipa");
const photoUrl = computed(() => user.value?.profile_photo_url);
const isCoordinator = computed(() => Boolean(staff.value.coordinator));

const rawSocialMedia = computed(() => {
    const p = staff.value.participant as
        | (typeof staff.value.participant & {
              socialMedia?: Record<string, string | null | undefined>;
          })
        | undefined;
    return p?.social_media ?? p?.socialMedia ?? {};
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

    const links: { platform: string; url: string; label: string }[] = [];

    for (const platform of supported) {
        const val = rawSocialMedia.value[platform];
        if (typeof val === "string" && val.trim() !== "") {
            links.push({
                platform,
                url: formatSocialUrl(platform, val),
                label:
                    platform === "email"
                        ? "Email"
                        : platform.charAt(0).toUpperCase() + platform.slice(1),
            });
        }
    }

    return links;
});
</script>

<template>
    <Card
        :image-src="photoUrl && !imageError ? photoUrl : undefined"
        :image-alt="name"
        class="h-full w-full"
    >
        <template v-if="!photoUrl || imageError" #image>
            <div
                class="flex h-full w-full items-center justify-center bg-neutral-900 text-neutral-600"
            >
                <Users class="h-12 w-12 opacity-40" />
            </div>
        </template>

        <template #header>
            <h4
                class="truncate text-base font-bold tracking-tight text-white transition-colors group-hover:text-neutral-200"
            >
                {{ name }}
            </h4>
            <p class="mt-1 line-clamp-1 text-xs text-neutral-400">
                <span v-if="isCoordinator" class="font-medium text-neutral-400">
                    Coordenador(a)
                </span>
                <span v-else class="text-neutral-400"> Membro da Equipa </span>
            </p>
        </template>

        <template #footer>
            <div
                v-if="socialLinks.length > 0"
                class="flex flex-wrap items-center gap-1.5"
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
            <div v-else class="text-[11px] text-neutral-600">SINF</div>
        </template>
    </Card>
</template>
