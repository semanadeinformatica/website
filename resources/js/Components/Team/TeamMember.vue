<script setup lang="ts">
import type Staff from "@/Types/Staff";
import { computed } from "vue";

const borderColor: Record<string, string> = {
    orange: "border-2023-orange",
    "teal-dark": "border-2023-teal-dark",
    "red-dark": "border-2023-red-dark",
    red: "border-2023-red",
    teal: "border-2023-teal",
};

const textColor: Record<string, string> = {
    orange: "text-2023-orange",
    "teal-dark": "text-2023-teal-dark",
    "red-dark": "text-2023-red-dark",
    red: "text-2023-red",
    teal: "text-2023-teal",
};

const iconColor: Record<string, string> = {
    orange: "#f29325",
    "teal-dark": "#025259",
    "red-dark": "#b15d5d",
    red: "#d94f04",
    teal: "#007172",
};

const socialIcon: Record<string, string> = {
    github: "io-logo-github",
    linkedin: "io-logo-linkedin",
    website: "io-globe",
};

const { color, staff } = defineProps<{
    color: string;
    staff: Staff;
}>();

const socialMedia = computed(() => {
    return Object.fromEntries(
        Object.entries(staff.student?.social_media ?? {}).filter(
            ([key, value]) =>
                ["github", "linkedin", "website"].includes(key) &&
                value != null,
        ),
    );
});
</script>

<template>
    <div
        class="group-hover group relative flex flex-col items-center overflow-y-hidden rounded-full border-4"
        :class="borderColor[color]"
    >
        <img
            :src="staff.student?.user?.profile_photo_url"
            :alt="`${staff.student?.user?.name}'s image`"
            class="w-40"
        />
        <div
            class="absolute -bottom-10 w-fit transition-all group-hover:bottom-4"
        >
            <a
                v-for="(social, key, idx) in socialMedia"
                :key="idx"
                :href="social"
            >
                <v-icon
                    :fill="iconColor[color]"
                    :name="socialIcon[key]"
                    scale="1.4"
                ></v-icon>
            </a>
        </div>
    </div>
    <p class="font-bold" :class="textColor[color]">
        {{ staff.student?.user?.name }}
    </p>
</template>

<style></style>
