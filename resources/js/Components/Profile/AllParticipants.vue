<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { usePage } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import route from "ziggy-js";

type Visitor = Participant & {
    can_see_cv: boolean;
    can_see_linkedin: boolean;
    can_see_all: boolean;
};

const allParticipants = usePage().props.allParticipants as Visitor[];
const user = usePage().props.user as Participant;

</script>

<template>
    <section
        v-if="allParticipants.length > 0"
        class="mt-10 flex flex-col items-center gap-3"
    >
        <div class="flex w-full flex-col md:flex-row md:justify-between">
            <h2 class="p-3 text-2xl font-bold text-text-color">
                Total de participantes: {{ allParticipants.length }}
            </h2>
            <a
                v-if="$page.props"
                class="inline-flex items-center justify-center border border-black bg-2025-dark px-4 py-2 text-center text-xl font-bold text-white shadow-2023-teal transition-shadow hover:shadow"
                :href="route('user.company.participants.cvs.all', { user })"
            >
                Descarregar CVs
            </a>
        </div>
        <div
            class="flex w-full flex-col items-center border border-black bg-2025-bg shadow-lg shadow-2023-teal"
        >
            <div
                v-for="visitor in allParticipants"
                :key="visitor.id"
                class="flex w-full items-center justify-between p-3 even:bg-white even:bg-opacity-20"
            >
                <a
                    :href="route('user.profile', { user: visitor.user })"
                    target="_blank"
                >
                    {{ visitor.user?.name ?? visitor.id }}
                </a>
                <div class="flex gap-4">
                    <a
                        v-if="
                            visitor.can_see_linkedin &&
                            visitor.social_media?.linkedin
                        "
                        :href="visitor.social_media.linkedin"
                        target="_blank"
                        title="Linkedin"
                    >
                        <OhVueIcon name="io-logo-linkedin" scale="1.3" />
                    </a>

                    <a
                        v-if="visitor.can_see_cv && visitor.cv_url"
                        :href="visitor.cv_url"
                        target="_blank"
                        title="CV"
                    >
                        <OhVueIcon name="io-document-text" scale="1.3" />
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>

