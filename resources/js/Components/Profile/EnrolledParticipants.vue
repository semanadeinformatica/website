<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { Link } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import route from "ziggy-js";
</script>

<template>
    <div
        v-if="($page.props.participants as unknown[]).length > 0"
        class="mt-10 flex flex-col items-center gap-4 border border-black bg-2023-bg p-5"
    >
        <div
            v-for="participant in $page.props.participants as Participant[]"
            :key="participant.id"
            class="flex w-full items-center justify-between gap-2"
        >
            {{ participant.user?.name ?? participant.id }}
            <Link :href="route('user.profile', participant.user)">
                <OhVueIcon name="io-open" scale="1.3" />
            </Link>
        </div>
    </div>
    <div v-else class="flex h-full flex-1 items-center">
        <p class="text-center text-4xl text-2023-teal">
            Ainda nenhum participante visitou a sua empresa.
        </p>
    </div>
</template>
