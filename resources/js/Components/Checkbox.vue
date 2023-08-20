<script setup lang="ts">
import { computed } from "vue";

const emit = defineEmits(["update:checked"]);

const props = withDefaults(
    defineProps<{ checked: boolean; value?: string }>(),
    {
        checked: false,
        value: undefined,
    },
);

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit("update:checked", val);
    },
});
</script>

<template>
    <input
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        class="border border-black bg-2023-bg shadow-2023-orange outline-none transition-all checked:border-black checked:bg-2023-teal hover:shadow checked:hover:border-black checked:hover:bg-2023-teal-dark"
    />
</template>
