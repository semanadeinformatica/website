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
            <div class="text-lg font-medium text-2023-red">
                <slot name="title" />
            </div>

            <div class="mt-4 text-sm text-2023-red-dark">
                <slot name="content" />
            </div>
        </div>

        <div
            class="flex flex-row justify-end bg-2023-bg px-6 py-4 text-right"
        >
            <slot name="footer" />
        </div>
    </Modal>
</template>
