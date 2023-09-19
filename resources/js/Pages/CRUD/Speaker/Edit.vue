<script setup lang="ts">
import ImageInput from "@/Components/ImageInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Event from "@/Types/Event";
import type Speaker from "@/Types/Speaker";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Speaker;
    with: {
        events: Event[];
    };
}

const { item: speaker } = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    name: speaker.name,
    title: speaker.title ?? "",
    description: speaker.description ?? "",
    organization: speaker.organization ?? "",
    event_id: speaker.event_id.toString(),
    social_media: {
        email: speaker?.social_media?.email ?? "",
        facebook: speaker?.social_media?.facebook ?? "",
        github: speaker?.social_media?.github ?? "",
        instagram: speaker?.social_media?.instagram ?? "",
        linkedin: speaker?.social_media?.linkedin ?? "",
        twitter: speaker?.social_media?.twitter ?? "",
        website: speaker?.social_media?.website ?? "",
    },
    photo: null as File | null,
});

const submit = () => {
    form.post(route("admin.speakers.update", speaker));
};
</script>

<template>
    <CardLayout title="Editar apresentador">
        <form class="contents" @submit.prevent="submit">
            <ImageInput
                id="photo"
                v-model="form.photo"
                :initial-preview="item.profile_photo_url"
                label="Foto de perfil"
                class="self-stretch"
                :error-message="form.errors.photo"
            />

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

            <TextInput
                v-model="form.event_id"
                type="select"
                required
                label="Evento"
                :error-message="form.errors.event_id"
            >
                <option
                    v-for="event in $props.with.events"
                    :key="event.id"
                    :value="event.id"
                >
                    {{ event.name }}
                </option>
            </TextInput>

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
