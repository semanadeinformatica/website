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
            <TextInput
                v-model="form.event_day_id"
                type="select"
                required
                label="Dia do evento"
            >
                <option
                    v-for="event_day in $props.with.eventDays"
                    :key="event_day.id"
                    :value="event_day.id"
                >
                    {{ event_day.date }}
                </option>
            </TextInput>

            <TextInput
                v-model="form.sponsor_id"
                type="select"
                required
                label="Empresa"
            >
                <option
                    v-for="sponsor in $props.with.sponsors"
                    :key="sponsor.id"
                    :value="sponsor.id"
                >
                    {{ sponsor.company?.user?.name ?? sponsor.company?.id }}
                </option>
            </TextInput>

            <PrimaryButton type="submit">Associar</PrimaryButton>
        </form>
    </CardLayout>
</template>
