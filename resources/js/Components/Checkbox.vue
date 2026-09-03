<script setup lang="ts">
import { computed } from "vue";

interface Props {
    checked?: boolean;
    modelValue?: boolean;
    value?: string;
    id?: string;
    name?: string;
    required?: boolean;
}

interface Emits {
    (event: "update:checked", value: boolean): void;
    (event: "update:modelValue", value: boolean): void;
}

const emit = defineEmits<Emits>();

const props = withDefaults(defineProps<Props>(), {
    checked: false,
    modelValue: undefined,
    value: undefined,
    id: undefined,
    name: undefined,
    required: false,
});

const proxyChecked = computed({
    get: () =>
        props.modelValue !== undefined ? props.modelValue : props.checked,
    set: (val: boolean) => {
        emit("update:checked", val);
        emit("update:modelValue", val);
    },
});
</script>

<template>
    <input
        :id="id"
        v-model="proxyChecked"
        type="checkbox"
        :name="name"
        :value="value"
        :required="required"
        class="text-sinf-crimson checked:border-sinf-crimson checked:bg-sinf-crimson focus:ring-sinf-crimson/30 h-4.5 w-4.5 cursor-pointer rounded-md border border-white/20 bg-white/5 transition-all duration-150 hover:border-white/40 hover:bg-white/10 focus:ring-2 focus:ring-offset-0 focus:outline-hidden"
    />
</template>
