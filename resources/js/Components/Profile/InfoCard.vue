<script setup lang="ts">
import ProfileCard from "./ProfileCard.vue";
import type { User } from "@/Types/User";

interface Props {
    user?: User;
    isStaff?: boolean;
    points?: number | null;
    showSocialMedia?: boolean;
}

withDefaults(defineProps<Props>(), {
    user: undefined,
    isStaff: false,
    points: undefined,
    showSocialMedia: true,
});
</script>

<template>
    <ProfileCard
        :user="user"
        :is-staff="isStaff"
        :points="points"
        :show-social-media="showSocialMedia"
    >
        <template v-if="$slots.avatar" #avatar>
            <slot name="avatar" />
        </template>
        <template v-if="$slots.details" #details>
            <slot name="details" />
        </template>
        <template v-if="$slots.actions" #actions>
            <slot name="actions" />
        </template>
    </ProfileCard>
</template>
