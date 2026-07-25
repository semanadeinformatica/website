<script setup lang="ts">
import { computed } from "vue";

interface Props {
    checked: boolean;
    value?: string;
}

interface Emits {
    (event: "update:checked", value: boolean): void;
}

const emit = defineEmits<Emits>();

const props = withDefaults(defineProps<Props>(), {
    checked: false,
    value: undefined,
});

const proxyChecked = computed({
    get: () => props.checked,
    set: (val) => emit("update:checked", val),
});
</script>

<template>
    <input
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        class="bg-2023-bg checked:bg-2025-blue checked:hover:bg-2023-teal-dark border border-black shadow-black/80 outline-hidden transition-all checked:border-black hover:shadow checked:hover:border-black"
    />
</template>
