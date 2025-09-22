<script setup lang="ts">
import type Staff from "@/Types/Staff";
import { OhVueIcon } from "oh-vue-icons";
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

const socialIcon: Record<string, string> = {
    github: "io-logo-github",
    linkedin: "io-logo-linkedin",
    website: "io-globe",
};

const bgColor: Record<string, string> = {
    orange: "bg-2023-orange",
    "teal-dark": "bg-2023-teal-dark",
    "red-dark": "bg-2023-red-dark",
    red: "bg-2023-red",
    teal: "bg-2023-teal",
};

const props = defineProps<{
    color: string;
    staff: Staff;
}>();

const staff = computed(() => props.staff);
const color = computed(() => props.color);

const socialMedia = computed(() => {
    return Object.fromEntries(
        Object.entries(staff.value.participant?.social_media ?? {}).filter(
            ([key, value]) =>
                ["github", "linkedin", "website"].includes(key) &&
                value != null,
        ),
    ) as Record<"github" | "linkedin" | "website", string>;
});
</script>

<template>
    <div
        class="group-hover group relative flex w-fit flex-col items-center overflow-y-hidden rounded-full border-4"
        :class="borderColor[color]"
    >
        <img
            :src="staff.participant?.user?.profile_photo_url"
            :alt="`${staff.participant?.user?.name}'s image`"
            class="h-40 w-40 object-cover"
        />
        <div
            v-if="Object.keys(socialMedia).length > 0"
            class="absolute -bottom-32 flex w-full flex-row items-center justify-center pb-10 pt-1 transition-all group-hover:-bottom-7"
            :class="bgColor[color]"
        >
            <a
                v-for="(social, key, idx) in socialMedia"
                :key="idx"
                :href="social"
                target="_blank"
            >
                <OhVueIcon
                    fill="white"
                    :name="socialIcon[key]"
                    scale="1.4"
                ></OhVueIcon>
            </a>
        </div>
    </div>
    <p class="max-w-[13em] text-center font-bold text-text-color">
        {{ staff.participant?.user?.name }}
    </p>
</template>

<style></style>
