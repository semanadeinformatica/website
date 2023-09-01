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
            <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
                <slot name="title" />
            </div>

            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                <slot name="content" />
            </div>
        </div>

        <div
            class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-right dark:bg-gray-800"
        >
            <slot name="footer" />
        </div>
    </Modal>
</template>
