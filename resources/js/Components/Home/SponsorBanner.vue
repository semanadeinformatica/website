<script setup lang="ts">
import Sponsor from "./Sponsor.vue";
import type SponsorType from "@/Types/Sponsor";
import type { CompanyUser } from "@/Types/User";

defineProps<{
    sponsors: SponsorType[];
    color: string;
    title: string;
    idx: number;
}>();
</script>

<template>
    <div class="w-full">
        <div class="mb-6 flex items-center justify-center">
            <div class="pill-container gap-2.5 px-4 py-1.5 shadow-none">
                <h3
                    class="text-xs font-semibold tracking-wider text-neutral-200 uppercase"
                >
                    {{ title }}
                </h3>
            </div>
        </div>

        <div
            v-if="sponsors.length"
            class="flex flex-wrap items-center justify-center gap-4 sm:gap-6"
        >
            <Sponsor
                v-for="sponsor in sponsors"
                :key="sponsor.id"
                :company="sponsor.company?.user as CompanyUser"
            />
        </div>
        <div v-else class="flex justify-center">
            <div
                class="pill-container px-6 py-2.5 font-mono text-xs text-neutral-500"
            >
                Em breve...
            </div>
        </div>
    </div>
</template>
