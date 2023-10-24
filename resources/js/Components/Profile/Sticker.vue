<script setup lang="ts">
import type Slot from "@/Types/Slot";
import { computed } from "vue";

const { sticker } = defineProps<{
    active?: boolean;
    sticker: Slot;
}>();

const completeness = computed(() => {
    return Math.min(1, (sticker.completed_count ?? 0) / sticker.total_quests);
});
</script>

<template>
    <div
        class="group relative h-52 w-52 overflow-hidden"
        :style="
            sticker.image_slot_url
                ? {
                      'mask-image': `url('${sticker.image_slot_url}')`,
                      'mask-repeat': 'no-repeat',
                      'mask-size': 'contain',
                      'mask-position': 'center',
                  }
                : ''
        "
    >
        <img
            v-if="sticker.image_slot_url"
            class="absolute inset-0 object-contain transition-all duration-500 group-hover:blur"
            :src="sticker.image_slot_url"
            alt=""
        />
        <div
            class="absolute inset-0 mix-blend-saturation"
            :style="{
                'background-image': `conic-gradient(transparent ${completeness}turn, black ${completeness}turn 1turn)`,
            }"
        />
        <div
            class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 p-10 text-center text-white opacity-0 transition-all duration-500 group-hover:opacity-100"
        >
            {{ sticker.name }}
        </div>
    </div>
</template>
