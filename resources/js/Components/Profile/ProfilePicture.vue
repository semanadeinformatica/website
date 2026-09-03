<script setup lang="ts">
import type { User } from "@/Types/User";

interface Props {
    item: User | undefined;
}

defineProps<Props>();
</script>

<template>
    <div class="flex items-end justify-center max-md:mb-6">
        <div
            class="h-48 w-48 overflow-hidden rounded-full border-4 border-solid border-white max-md:ml-6"
        >
            <img
                :src="item?.profile_photo_url"
                :alt="item?.name"
                class="h-full w-full object-cover"
            />
        </div>
    </div>
</template>

