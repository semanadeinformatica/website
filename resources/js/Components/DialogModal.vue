<script setup lang="ts">
import Modal, { type Props, type Emits } from "./Modal.vue";

defineProps<Partial<Props>>();

const emit = defineEmits<Emits>();

const close = () => emit("close");
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 py-4">
            <div class="text-lg font-bold text-white">
                <slot name="title" />
            </div>

            <div class="mt-4 space-y-4 text-sm text-white">
                <slot name="content"/>
            </div>
        </div>

        <div class="flex flex-row justify-end bg-2025-blue px-4 py-4 text-right">
            <slot name="footer" />
        </div>
    </Modal>
</template>
