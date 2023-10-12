<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { Link, usePage } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import route from "ziggy-js";

type Visitor = Participant & {
    can_see_cv: boolean;
    can_see_linkedin: boolean;
};

const visitors = usePage().props.participants as Visitor[];

console.log(visitors);
</script>

<template>
    <div
        v-if="visitors.length > 0"
        class="mt-10 flex flex-col items-center border border-black bg-2023-bg shadow-lg shadow-2023-teal"
    >
        <div
            v-for="visitor in visitors"
            :key="visitor.id"
            class="flex w-full items-center justify-between p-3 even:bg-2023-orange even:bg-opacity-20"
        >
            {{ visitor.user?.name ?? visitor.id }}
            <div class="flex gap-4">
                <template v-if="visitor.can_see_linkedin">
                    <span v-if="visitor.social_media?.linkedin">
                        <a :href="visitor.social_media.linkedin">
                            <OhVueIcon name="io-logo-linkedin" scale="1.3" />
                        </a>
                    </span>
                    <span v-else> Sem LinkedIn </span>
                </template>
                <Link
                    v-if="visitor.can_see_cv"
                    :href="route('user.profile', visitor.user)"
                >
                    <OhVueIcon name="io-open" scale="1.3" />
                </Link>
            </div>
        </div>
    </div>
    <div v-else class="flex h-full flex-1 items-center justify-center">
        <p class="text-center text-2xl text-2023-teal">
            Ainda nenhum participante visitou a sua empresa.
        </p>
    </div>
</template>
