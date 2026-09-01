<script setup lang="ts">
interface Props {
    label: string;
    timestamp?: string;
    type?: "start" | "end";
}

defineProps<Props>();

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
    <div class="flex items-center gap-3 py-2">
        <div
            class="pill-container gap-2 px-3.5 py-1 text-xs text-neutral-300 shadow-none"
        >
            <span class="font-medium text-white">{{ label }}</span>
            <span v-if="timestamp" class="text-neutral-400">
                • {{ formatTime(timestamp) }}
            </span>
        </div>
    </div>
</template>
