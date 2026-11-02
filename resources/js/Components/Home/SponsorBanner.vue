<script setup lang="ts">
import Sponsor from "./Sponsor.vue";
import ComingSoon from "@/Components/UI/ComingSoon.vue";
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
            <h3
                class="text-xs font-semibold tracking-wider text-neutral-400 uppercase"
            >
                {{ title }}
            </h3>
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
        <ComingSoon v-else class="py-6" />
    </div>
</template>
