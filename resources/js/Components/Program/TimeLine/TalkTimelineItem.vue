<script setup lang="ts">
import type Event from "@/Types/Event";
import { isSpeaker } from "@/Types/User";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";
import Card from "@/Components/UI/Card.vue";
import { Clock, MapPin, ArrowRight } from "@lucide/vue";

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

        <Card as="article" padding="p-6 sm:p-7">
            <template #header>
                <div class="mb-4 flex flex-wrap items-center gap-2.5">
                    <div
                        v-if="event.time_start"
                        class="pill-container gap-1.5 px-3 py-1 text-xs text-neutral-300 shadow-none"
                    >
                        <Clock :size="14" class="text-neutral-400" />
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
                        <MapPin :size="14" class="text-neutral-400" />
                        <span>{{ event.location }}</span>
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
                        <ArrowRight
                            :size="16"
                            class="text-neutral-500 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-white"
                        />
                    </Link>
                </h3>
            </template>

            <p
                v-if="event.topic"
                class="mt-2 text-sm leading-relaxed text-neutral-300 sm:text-base"
            >
                {{ event.topic }}
            </p>

            <template v-if="speakers.length > 0" #footer>
                <div
                    class="flex w-full flex-wrap items-center gap-3 border-t border-white/8 pt-4"
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
            </template>
        </Card>
    </div>
</template>
