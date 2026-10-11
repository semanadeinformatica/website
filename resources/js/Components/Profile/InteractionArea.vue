<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { type Tabs } from "@/Types/ProfilePage";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import { Ticket, Award, UserCheck, Users } from "@lucide/vue";

type Props = {
    buttons: Tabs;
};

const props = defineProps<Props>();

const buttons = computed(() => props.buttons);

const buttonKeys = computed(() => Object.keys(buttons.value));

const selectedType = ref<string>(buttonKeys.value[0] ?? "");

watch(
    buttonKeys,
    (keys) => {
        if (!keys.includes(selectedType.value) && keys.length > 0) {
            selectedType.value = keys[0];
        }
    },
    { immediate: true },
);

const tabOptions = computed<PillOption[]>(() => {
    return Object.entries(buttons.value).map(([id, item]) => {
        let icon = undefined;
        if (id === "ticket") icon = Ticket;
        else if (id === "sticker") icon = Award;
        else if (id === "visitHistory") icon = UserCheck;
        else if (id === "allParticipants") icon = Users;

        return {
            id,
            label: item.label,
            icon,
        };
    });
});

const view = computed(() => {
    return buttons.value[selectedType.value]?.component;
});
</script>

<template>
    <section
        v-if="tabOptions.length > 0"
        class="flex w-full flex-1 flex-col pt-4"
    >
        <div v-if="tabOptions.length > 1" class="mb-8 flex justify-center">
            <PillSelector
                v-model="selectedType"
                :items="tabOptions"
                size="md"
            />
        </div>

        <div class="w-full">
            <KeepAlive>
                <component :is="view" :key="selectedType" />
            </KeepAlive>
        </div>
    </section>
</template>
