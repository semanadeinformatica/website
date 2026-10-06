<script setup lang="ts">
import type { User } from "@/Types/User";

interface Props {
    item: User | undefined;
    size?: "sm" | "md" | "lg";
}

withDefaults(defineProps<Props>(), {
    size: "lg",
});
</script>

<template>
    <div class="relative shrink-0 select-none">
        <div
            class="overflow-hidden rounded-full bg-neutral-900 ring-2 ring-white/10 transition-all duration-300"
            :class="{
                'h-18 w-18 sm:h-22 sm:w-22': size === 'sm',
                'h-24 w-24 sm:h-28 sm:w-28': size === 'md',
                'h-28 w-28 sm:h-32 sm:w-32': size === 'lg',
            }"
        >
            <img
                v-if="item?.profile_photo_url"
                :src="item.profile_photo_url"
                :alt="item.name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-neutral-800 text-neutral-300"
            >
                <span
                    class="font-bold uppercase"
                    :class="{
                        'text-xl sm:text-2xl': size === 'sm',
                        'text-2xl sm:text-3xl': size === 'md' || size === 'lg',
                    }"
                >
                    {{ item?.name?.charAt(0) ?? "U" }}
                </span>
            </div>
        </div>
    </div>
</template>
