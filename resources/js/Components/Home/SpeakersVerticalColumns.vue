<script setup lang="ts">
import { ref, computed } from "vue";
import type { User, SpeakerUser } from "@/Types/User";
import SocialIcon from "@/Components/UI/SocialIcon.vue";
import Modal from "@/Components/UI/Modal.vue";
import { useScrollReveal } from "@/Composables/useScrollReveal";

interface Props {
    speakers: User[];
}

const props = defineProps<Props>();

const { targetRef, isVisible } = useScrollReveal({
    threshold: 0.1,
    once: false,
});

const selectedSpeaker = ref<User | null>(null);
const isBioModalOpen = ref(false);

function openBioModal(speaker: User) {
    selectedSpeaker.value = speaker;
    isBioModalOpen.value = true;
}

function closeBioModal() {
    isBioModalOpen.value = false;
    selectedSpeaker.value = null;
}

function getSpeakerDisplayName(user: User | null): string {
    return user?.name ?? "";
}

function getSpeakerTitle(user: User | null): string {
    if (!user) return "";
    const u = user as Partial<SpeakerUser>;
    return u.usertype?.title ?? "";
}

function getSpeakerOrganization(user: User | null): string {
    if (!user) return "";
    const u = user as Partial<SpeakerUser>;
    return u.usertype?.organization ?? "";
}

function getSpeakerDescriptionHtml(user: User | null): string {
    if (!user) return "";
    const u = user as Partial<SpeakerUser>;
    return u.usertype?.description_html ?? "";
}

interface SpeakerSocialLink {
    platform: string;
    url: string;
    label: string;
}

const SUPPORTED_SOCIAL_PLATFORMS = [
    "github",
    "linkedin",
    "website",
    "twitter",
    "instagram",
    "facebook",
    "email",
] as const;

function formatSocialUrl(platform: string, val: string): string {
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
    if (platform === "facebook") {
        return `https://facebook.com/${trimmed.replace(/^@/, "")}`;
    }
    return `https://${trimmed}`;
}

const selectedSpeakerSocialLinks = computed<SpeakerSocialLink[]>(() => {
    if (!selectedSpeaker.value) return [];
    const u = selectedSpeaker.value as Partial<SpeakerUser>;
    const raw = (u.usertype?.social_media ?? {}) as Record<string, unknown>;

    const links: SpeakerSocialLink[] = [];
    for (const platform of SUPPORTED_SOCIAL_PLATFORMS) {
        const val = raw[platform];
        if (typeof val === "string" && val.trim() !== "") {
            links.push({
                platform,
                url: formatSocialUrl(platform, val),
                label: platform.charAt(0).toUpperCase() + platform.slice(1),
            });
        }
    }
    return links;
});

const COLUMN_COUNT = 6;

function getColumnVisibilityClass(cIdx: number): string {
    if (cIdx >= 4) return "hidden lg:flex";
    if (cIdx === 3) return "hidden md:flex";
    if (cIdx === 2) return "hidden sm:flex";
    return "flex";
}

const columns = computed(() => {
    if (!props.speakers || props.speakers.length === 0) {
        return Array.from({ length: COLUMN_COUNT }, () => []);
    }

    const all = props.speakers;
    const n = all.length;

    return Array.from({ length: COLUMN_COUNT }, (_, cIdx) => {
        const offset = (cIdx * 3) % n;
        const reordered: User[] = [];
        for (let i = 0; i < n; i++) {
            reordered.push(all[(i + offset) % n]);
        }

        const trackItems: User[] = [];
        while (trackItems.length < 10) {
            trackItems.push(...reordered);
        }
        return trackItems;
    });
});
</script>

<template>
    <div
        ref="targetRef"
        class="relative h-screen min-h-dvh max-h-dvh w-full overflow-hidden"
    >
        <div
            class="pointer-events-none absolute top-16 inset-x-0 z-30 flex flex-col items-center justify-center px-4 text-center sm:top-20 lg:top-20"
        >
            <h2
                class="text-2xl font-black tracking-tight text-white drop-shadow-md transition-all duration-700 ease-in-out sm:text-4xl lg:text-5xl"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0',
                ]"
            >
                Conhece os oradores desta edição!
            </h2>

            <p
                class="mt-1.5 max-w-xs text-xs text-neutral-400 transition-all duration-700 ease-in-out delay-150 sm:max-w-md sm:text-sm md:max-w-lg md:text-base"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0',
                ]"
            >
                Descobre as ideias, experiências e tendências que estão a moldar o futuro da tecnologia.
            </p>
        </div>

        <template v-if="speakers.length !== 0">
            <div
                class="speaker-wall-container relative h-full w-full overflow-hidden transition-all duration-1000 ease-in-out delay-150"
                :class="[
                    isVisible
                        ? 'opacity-100 scale-100'
                        : 'opacity-0 scale-95',
                ]"
            >
                <div
                    class="speaker-columns-mask grid h-full w-full grid-cols-2 gap-2.5 px-3 sm:grid-cols-3 sm:gap-3.5 sm:px-4 md:grid-cols-4 lg:grid-cols-6 lg:gap-4"
                >
                    <div
                        v-for="(col, cIdx) in columns"
                        :key="`col-${cIdx}`"
                        class="column-track-wrapper relative h-full overflow-hidden"
                        :class="getColumnVisibilityClass(cIdx)"
                    >
                        <div
                            class="column-track"
                            :class="[
                                cIdx % 2 === 0
                                    ? 'animate-scroll-up'
                                    : 'animate-scroll-down',
                                `speed-${(cIdx % 3) + 1}`,
                            ]"
                        >
                            <div class="track-sequence">
                                <button
                                    v-for="(speaker, sIdx) in col"
                                    :key="`c${cIdx}-s${speaker.id}-${sIdx}`"
                                    type="button"
                                    class="speaker-card group relative w-full shrink-0 cursor-pointer overflow-hidden rounded-xl border border-white/6 bg-black/40 p-2 text-left opacity-50 backdrop-blur-xs transition-opacity duration-300 hover:opacity-100 active:opacity-100 sm:rounded-2xl sm:p-2.5 sm:opacity-30 md:p-3"
                                    @click="openBioModal(speaker)"
                                >
                                    <div
                                        class="relative aspect-square w-full overflow-hidden rounded-lg bg-neutral-900/80 sm:rounded-xl"
                                    >
                                        <img
                                            :src="speaker.profile_photo_url"
                                            :alt="getSpeakerDisplayName(speaker)"
                                            class="h-full w-full object-cover grayscale filter transition-all duration-300 group-hover:grayscale-0"
                                            loading="lazy"
                                        />
                                        <div
                                            class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/75 via-transparent to-transparent opacity-60"
                                        />
                                    </div>

                                    <div class="mt-1.5 sm:mt-2">
                                        <h3
                                            class="truncate text-xs font-bold text-neutral-200 transition-colors group-hover:text-white sm:text-sm"
                                        >
                                            {{ getSpeakerDisplayName(speaker) }}
                                        </h3>
                                        <p
                                            class="mt-0.5 truncate text-[10px] text-neutral-400 group-hover:text-neutral-300 sm:text-[11px]"
                                        >
                                            {{
                                                getSpeakerTitle(speaker) ||
                                                getSpeakerOrganization(
                                                    speaker,
                                                ) ||
                                                "Speaker"
                                            }}
                                        </p>
                                    </div>
                                </button>
                            </div>

                            <div class="track-sequence" aria-hidden="true">
                                <button
                                    v-for="(speaker, sIdx) in col"
                                    :key="`c${cIdx}-s${speaker.id}-dup-${sIdx}`"
                                    type="button"
                                    tabindex="-1"
                                    class="speaker-card group relative w-full shrink-0 cursor-pointer overflow-hidden rounded-xl border border-white/6 bg-black/40 p-2 text-left opacity-50 backdrop-blur-xs transition-opacity duration-300 hover:opacity-100 active:opacity-100 sm:rounded-2xl sm:p-2.5 sm:opacity-30 md:p-3"
                                    @click="openBioModal(speaker)"
                                >
                                    <div
                                        class="relative aspect-square w-full overflow-hidden rounded-lg bg-neutral-900/80 sm:rounded-xl"
                                    >
                                        <img
                                            :src="speaker.profile_photo_url"
                                            :alt="getSpeakerDisplayName(speaker)"
                                            class="h-full w-full object-cover grayscale filter transition-all duration-300 group-hover:grayscale-0"
                                            loading="lazy"
                                        />
                                        <div
                                            class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/75 via-transparent to-transparent opacity-60"
                                        />
                                    </div>

                                    <div class="mt-1.5 sm:mt-2">
                                        <h3
                                            class="truncate text-xs font-bold text-neutral-200 transition-colors group-hover:text-white sm:text-sm"
                                        >
                                            {{ getSpeakerDisplayName(speaker) }}
                                        </h3>
                                        <p
                                            class="mt-0.5 truncate text-[10px] text-neutral-400 group-hover:text-neutral-300 sm:text-[11px]"
                                        >
                                            {{
                                                getSpeakerTitle(speaker) ||
                                                getSpeakerOrganization(
                                                    speaker,
                                                ) ||
                                                "Speaker"
                                            }}
                                        </p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <Modal :show="isBioModalOpen" max-width="lg" @close="closeBioModal">
            <div
                v-if="selectedSpeaker"
                class="relative max-h-[85vh] overflow-y-auto rounded-2xl border border-white/10 bg-neutral-950 p-5 text-white sm:p-8"
            >
                <div class="flex items-start gap-4 sm:gap-5">
                    <img
                        :src="selectedSpeaker.profile_photo_url"
                        :alt="getSpeakerDisplayName(selectedSpeaker)"
                        class="h-16 w-16 rounded-xl object-cover ring-2 ring-sinf-primary/50 sm:h-24 sm:w-24 sm:rounded-2xl"
                    />
                    <div class="flex-1">
                        <h3 class="text-lg font-black text-white sm:text-2xl">
                            {{ getSpeakerDisplayName(selectedSpeaker) }}
                        </h3>
                        <p
                            v-if="getSpeakerTitle(selectedSpeaker)"
                            class="text-xs font-medium text-sinf-primary sm:text-sm"
                        >
                            {{ getSpeakerTitle(selectedSpeaker) }}
                            <span
                                v-if="getSpeakerOrganization(selectedSpeaker)"
                                class="text-neutral-400"
                            >
                                • {{ getSpeakerOrganization(selectedSpeaker) }}
                            </span>
                        </p>
                        <p
                            v-else-if="
                                getSpeakerOrganization(selectedSpeaker)
                            "
                            class="text-xs font-medium text-neutral-400 sm:text-sm"
                        >
                            {{ getSpeakerOrganization(selectedSpeaker) }}
                        </p>

                        <div
                            v-if="selectedSpeakerSocialLinks.length > 0"
                            class="mt-2.5 flex flex-wrap items-center gap-2 sm:mt-3"
                        >
                            <a
                                v-for="link in selectedSpeakerSocialLinks"
                                :key="link.platform"
                                :href="link.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                :aria-label="`${getSpeakerDisplayName(selectedSpeaker)} - ${link.label}`"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-neutral-400 transition-all hover:border-sinf-primary/60 hover:bg-sinf-primary/20 hover:text-white sm:h-9 sm:w-9"
                            >
                                <SocialIcon
                                    :platform="link.platform"
                                    :size="15"
                                />
                            </a>
                        </div>
                    </div>
                </div>

                <div
                    v-if="getSpeakerDescriptionHtml(selectedSpeaker)"
                    class="mt-4 border-t border-white/10 pt-4 text-xs leading-relaxed text-neutral-300 sm:mt-6 sm:pt-5 sm:text-sm"
                    v-html="getSpeakerDescriptionHtml(selectedSpeaker)"
                />
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.speaker-columns-mask {
    mask-image: linear-gradient(
        to bottom,
        transparent 0%,
        rgba(0, 0, 0, 0.05) 3%,
        rgba(0, 0, 0, 0.6) 12%,
        black 22%,
        black 78%,
        rgba(0, 0, 0, 0.6) 88%,
        rgba(0, 0, 0, 0.05) 97%,
        transparent 100%
    );
    -webkit-mask-image: linear-gradient(
        to bottom,
        transparent 0%,
        rgba(0, 0, 0, 0.05) 3%,
        rgba(0, 0, 0, 0.6) 12%,
        black 22%,
        black 78%,
        rgba(0, 0, 0, 0.6) 88%,
        rgba(0, 0, 0, 0.05) 97%,
        transparent 100%
    );
}

.column-track-wrapper {
    height: 100%;
}

.column-track {
    display: flex;
    flex-direction: column;
    width: 100%;
    will-change: transform;
}

.track-sequence {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    padding-bottom: 0.625rem;
}

@media (min-width: 640px) {
    .track-sequence {
        gap: 0.75rem;
        padding-bottom: 0.75rem;
    }
}

.animate-scroll-up.speed-1 {
    animation: scrollUp 42s linear infinite;
}
.animate-scroll-up.speed-2 {
    animation: scrollUp 48s linear infinite;
}
.animate-scroll-up.speed-3 {
    animation: scrollUp 44s linear infinite;
}

.animate-scroll-down.speed-1 {
    animation: scrollDown 42s linear infinite;
}
.animate-scroll-down.speed-2 {
    animation: scrollDown 48s linear infinite;
}
.animate-scroll-down.speed-3 {
    animation: scrollDown 44s linear infinite;
}

@keyframes scrollUp {
    0% {
        transform: translateY(0);
    }
    100% {
        transform: translateY(-50%);
    }
}

@keyframes scrollDown {
    0% {
        transform: translateY(-50%);
    }
    100% {
        transform: translateY(0);
    }
}
</style>
