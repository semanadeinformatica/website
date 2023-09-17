<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type EventDay from "@/Types/EventDay";
import type Sponsor from "@/Types/Sponsor";
import type Stand from "@/Types/Stand";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Stand;
    with: {
        sponsors: Sponsor[];
        eventDays: EventDay[];
    };
}

const { item: stand } = defineProps<Props>();

const form = useForm({
    event_day_id: stand.event_day_id,
    sponsor_id: stand.sponsor_id,
});

const submit = () => {
    form.post(route("admin.stands.store"));
};
</script>

<template>
    <CardLayout title="Associar Banca">
        <form class="contents" @submit.prevent="submit">
            <select v-model="form.event_day_id" required class="self-stretch">
                <option value="" disabled selected hidden>Dia do evento</option>
                <option
                    v-for="event_day in $props.with.eventDays"
                    :key="event_day.id"
                    :value="event_day.id"
                >
                    {{ event_day.date }}
                </option>
            </select>

            <select v-model="form.sponsor_id" required class="self-stretch">
                <option value="" disabled selected hidden>Empresa</option>
                <option
                    v-for="sponsor in $props.with.sponsors"
                    :key="sponsor.id"
                    :value="sponsor.id"
                >
                    {{ sponsor.company.user?.name ?? sponsor.company.id }}
                </option>
            </select>

            <PrimaryButton type="submit">Associar</PrimaryButton>
        </form>
    </CardLayout>
</template>
