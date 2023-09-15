<script setup lang="ts">
import type { User } from "@/Types/User";

interface Props {
    item: User;
}

defineProps<Props>();
</script>

<template>
    <div class="flex items-end">
        <div
            class="h-48 w-48 overflow-hidden rounded-full border-4 border-solid border-2023-teal"
        >
            <img
                :src="item.profile_photo_url"
                alt="Profile Picture"
                class="h-full w-full object-cover"
            />
        </div>
        <button>
            <v-icon name="io-share" fill="2023-teal" scale="1.3"></v-icon>
        </button>
    </div>
</template>
