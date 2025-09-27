<script setup lang="ts">
import type Slot from "@/Types/Slot";
import Sticker from "./Sticker.vue";
</script>

<template>
    <p
        v-if="($page.props.slots as Slot[]).length === 0"
        class="flex w-full flex-auto items-center justify-center pt-8 text-center text-2xl font-bold text-text-color"
    >
        Ainda não há conquistas para completares. Fica de olho nesta secção!
    </p>
    <div
        v-else
        class="flex w-full flex-row flex-wrap items-center justify-center gap-8 place-self-center self-center pt-8"
    >
        <Sticker
            v-for="item in $page.props.slots as Slot[]"
            :key="item.id"
            :sticker="item"
        />
    </div>
</template>
