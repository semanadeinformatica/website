<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type EventDay from "@/Types/EventDay";
import type Sponsor from "@/Types/Sponsor";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    with: {
        sponsors: Sponsor[];
        eventDays: EventDay[];
    };
}

defineProps<Props>();

const form = useForm({
    event_day_id: "",
    sponsor_id: "",
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
                :error-message="form.errors.event_day_id"
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
                :error-message="form.errors.sponsor_id"
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
