<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { Link } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import route from "ziggy-js";
</script>

<template>
    <div
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
</template>
