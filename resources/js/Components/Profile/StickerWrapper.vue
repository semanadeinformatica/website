<script setup lang="ts">
import type Slot from "@/Types/Slot";
import Sticker from "./Sticker.vue";
import PillSelector from "@/Components/UI/PillSelector.vue";
</script>

<template>
    <div
        v-if="($page.props.slots as Slot[]).length === 0"
        class="flex flex-col items-center justify-center py-20 text-center"
    >
        <PillSelector
            :items="[{ id: 'empty', label: 'Sem conquistas', disabled: true }]"
            size="sm"
            :wrap="false"
            container-class="mb-4"
        />
        <p class="max-w-md text-sm text-neutral-400">
            Ainda não há conquistas para completares. Fica de olho nas
            atividades e bancas da SINF!
        </p>
    </div>
    <div
        v-else
        class="flex w-full flex-row flex-wrap items-stretch justify-center gap-6 pt-4 sm:gap-8"
    >
        <Sticker
            v-for="item in $page.props.slots as Slot[]"
            :key="item.id"
            :sticker="item"
        />
    </div>
</template>
