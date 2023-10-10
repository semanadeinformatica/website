<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { Link } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import route from "ziggy-js";
</script>

<template>
    <div
        v-if="($page.props.participants as unknown[]).length > 0"
        class="mt-10 flex flex-col gap-4 items-center border border-black p-5 bg-2023-bg"
    >
        <div
            v-for="participant in ($page.props.participants as Participant[])"
            :key="participant.id"
            class="flex items-center gap-2 justify-between w-full"
        >
            {{ participant.user?.name ?? participant.id }}
            <Link :href="route('user.profile', participant.user)">
                <OhVueIcon name="io-open" scale="1.3" />
            </Link>
        </div>
    </div>
    <div v-else class="h-full flex items-center flex-1">
        <p class="text-center text-4xl text-2023-teal">Ainda nenhum participante visitou a sua empresa.</p>
    </div>
</template>
