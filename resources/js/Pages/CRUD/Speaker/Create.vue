<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Event from "@/Types/Event";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    with: {
        events: Event[];
    };
}

defineProps<Props>();

const form = useForm({
    name: "",
    title: "",
    description: "",
    organization: "",
    event_id: "",
    social_media: {
        email: "",
        facebook: "",
        github: "",
        instagram: "",
        linkedin: "",
        twitter: "",
        website: "",
    },
});

const submit = () => {
    form.post(route("admin.speakers.store"));
};
</script>

<template>
    <CardLayout title="Criar apresentador">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="name"
                v-model="form.name"
                label="Nome"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.name"
            />

            <TextInput
                id="title"
                v-model="form.title"
                label="Título"
                type="text"
                :error-message="form.errors.title"
            />

            <TextInput
                id="description"
                v-model="form.description"
                label="Descrição"
                type="text"
                :error-message="form.errors.description"
            />

            <TextInput
                id="organization"
                v-model="form.organization"
                label="Organização"
                type="text"
                :error-message="form.errors.organization"
            />

            <details class="self-stretch">
                <summary>Redes sociais</summary>

                <div class="mt-4 flex flex-col gap-4">
                    <TextInput
                        id="social_media.email"
                        v-model="form.social_media.email"
                        label="Email"
                        type="email"
                        autocomplete="email"
                        :error-message="form.errors.social_media"
                    />

                    <TextInput
                        id="social_media.facebook"
                        v-model="form.social_media.facebook"
                        label="Facebook"
                        type="text"
                    />

                    <TextInput
                        id="social_media.github"
                        v-model="form.social_media.github"
                        label="Github"
                        type="text"
                    />

                    <TextInput
                        id="social_media.instagram"
                        v-model="form.social_media.instagram"
                        label="Instagram"
                        type="text"
                    />

                    <TextInput
                        id="social_media.linkedin"
                        v-model="form.social_media.linkedin"
                        label="Linkedin"
                        type="text"
                    />

                    <TextInput
                        id="social_media.twitter"
                        v-model="form.social_media.twitter"
                        label="Twitter"
                        type="text"
                    />

                    <TextInput
                        id="social_media.website"
                        v-model="form.social_media.website"
                        label="Website"
                        type="url"
                    />
                </div>
            </details>

            <select v-model="form.event_id" required class="self-stretch">
                <option value="" disabled selected hidden>Evento</option>
                <option
                    v-for="event in $props.with.events"
                    :key="event.id"
                    :value="event.id"
                >
                    {{ event.name }}
                </option>
            </select>

            <PrimaryButton type="submit">Criar</PrimaryButton>
        </form>
    </CardLayout>
</template>
