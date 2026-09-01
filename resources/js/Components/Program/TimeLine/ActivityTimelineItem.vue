<script setup lang="ts">
import type Event from "@/Types/Event";
import { isSpeaker } from "@/Types/User";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";

interface Props {
    event: Event;
}

const props = defineProps<Props>();
const event = computed(() => props.event);
const speakers = computed(() => event.value.users?.filter(isSpeaker) ?? []);

const formatTime = (time?: string): string => {
    if (!time) return "";
    const isoString = time.includes("T") ? time : `1970-01-01T${time}.000000Z`;
    try {
        const d = new Date(isoString);
        return d.toLocaleTimeString("pt-PT", {
            hour: "2-digit",
            minute: "2-digit",
            timeZone: "Europe/Lisbon",
        });
    } catch {
        return time.slice(0, 5);
    }
};
</script>

<template>
    <div class="group relative">
        <div
            class="absolute top-8 -left-9.25 hidden h-2.5 w-2.5 rounded-full bg-neutral-600 ring-4 ring-black transition-all duration-300 group-hover:scale-125 sm:flex"
        />

        <article
            class="rounded-3xl border border-white/8 bg-black/50 p-6 shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out hover:scale-[1.01] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-7"
        >
            <div class="mb-4 flex flex-wrap items-center gap-2.5">
                <div
                    v-if="event.time_start"
                    class="pill-container gap-1.5 px-3 py-1 text-xs text-neutral-300 shadow-none"
                >
                    <svg
                        class="h-3.5 w-3.5 text-neutral-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <span>
                        {{ formatTime(event.time_start) }}
                        <template v-if="event.time_end">
                            - {{ formatTime(event.time_end) }}
                        </template>
                    </span>
                </div>

                <div
                    v-if="event.location"
                    class="pill-container gap-1.5 px-3 py-1 text-xs text-neutral-300 shadow-none"
                >
                    <svg
                        class="h-3.5 w-3.5 text-neutral-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                    <span>{{ event.location }}</span>
                </div>

                <div
                    v-if="event.capacity"
                    class="pill-container gap-1.5 px-3 py-1 text-xs text-neutral-300 shadow-none"
                >
                    <svg
                        class="h-3.5 w-3.5 text-neutral-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                    </svg>
                    <span>{{ event.capacity }} vagas</span>
                </div>
            </div>

            <h3 class="text-xl font-bold tracking-tight sm:text-2xl">
                <Link
                    :href="route('event.show', { event: event.id })"
                    class="inline-flex items-center gap-2 text-white transition-colors hover:text-neutral-200"
                    preserve-state
                    preserve-scroll
                >
                    <span>{{ event.name }}</span>
                    <svg
                        class="h-4 w-4 text-neutral-500 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                        />
                    </svg>
                </Link>
            </h3>

            <p
                v-if="event.topic"
                class="mt-2 text-sm leading-relaxed text-neutral-300 sm:text-base"
            >
                {{ event.topic }}
            </p>

            <div
                v-if="speakers.length > 0"
                class="mt-5 flex flex-wrap items-center gap-3 border-t border-white/8 pt-4"
            >
                <div
                    v-for="user in speakers"
                    :key="user.id"
                    class="flex items-center gap-2.5 rounded-full bg-white/5 py-1 pr-3.5 pl-1 ring-1 ring-white/10 backdrop-blur-xs"
                >
                    <img
                        v-if="user.profile_photo_url"
                        :src="user.profile_photo_url"
                        :alt="user.name"
                        class="h-7 w-7 rounded-full object-cover ring-1 ring-white/20"
                    />
                    <div
                        v-else
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-neutral-800 text-xs font-bold text-white"
                    >
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-white">
                            {{ user.name }}
                        </span>
                        <span
                            v-if="user.usertype?.organization"
                            class="text-[11px] text-neutral-400"
                        >
                            {{ user.usertype.organization }}
                        </span>
                    </div>
                </div>
            </div>
        </article>
    </div>
</template>
