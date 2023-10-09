<script setup lang="ts">
import ImageInput from "@/Components/ImageInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type { User } from "@/Types/User";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: User;
}

const { item: user } = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    name: user.name,
    email: user.email,
    type: (user.usertype_type.split("\\").pop() ?? "").toLowerCase() as
        | "participant"
        | "company"
        | "speaker"
        | "admin",
    title:
        user.usertype_type === "App\\Models\\Speaker"
            ? user.usertype?.title ?? ""
            : "",
    description:
        user.usertype_type === "App\\Models\\Company" ||
        user.usertype_type === "App\\Models\\Speaker"
            ? user.usertype?.description ?? ""
            : "",
    organization:
        user.usertype_type === "App\\Models\\Speaker"
            ? user.usertype?.organization ?? ""
            : "",
    public_email:
        user?.usertype_type !== "App\\Models\\Admin"
            ? user?.usertype?.social_media?.email ?? ""
            : "",
    facebook:
        user?.usertype_type !== "App\\Models\\Admin"
            ? user?.usertype?.social_media?.facebook ?? ""
            : "",
    github:
        user?.usertype_type !== "App\\Models\\Admin"
            ? user?.usertype?.social_media?.github ?? ""
            : "",
    instagram:
        user?.usertype_type !== "App\\Models\\Admin"
            ? user?.usertype?.social_media?.instagram ?? ""
            : "",
    linkedin:
        user?.usertype_type !== "App\\Models\\Admin"
            ? user?.usertype?.social_media?.linkedin ?? ""
            : "",
    twitter:
        user?.usertype_type !== "App\\Models\\Admin"
            ? user?.usertype?.social_media?.twitter ?? ""
            : "",
    website:
        user?.usertype_type !== "App\\Models\\Admin"
            ? user?.usertype?.social_media?.website ?? ""
            : "",
    photo: null as File | null,
});

const submit = () => {
    form.post(route("admin.users.update", user));
};
</script>

<template>
    <CardLayout title="Editar utilizador">
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
                id="email"
                v-model="form.email"
                label="Email"
                type="email"
                required
                autocomplete="email"
                :error-message="form.errors.email"
            />

            <TextInput
                v-model="form.type"
                type="select"
                label="Tipo de utilizador"
                required
                disabled
                :error-message="form.errors.type"
            >
                <option value="participant">Participante</option>
                <option value="company">Empresa</option>
                <option value="speaker">Orador</option>
                <option value="admin">Administrador</option>
            </TextInput>

            <TextInput
                v-if="form.type === 'speaker'"
                id="title"
                v-model="form.title"
                label="Título"
                type="text"
                :error-message="form.errors.title"
            />

            <TextInput
                v-if="form.type === 'company' || form.type === 'speaker'"
                id="description"
                v-model="form.description"
                label="Descrição"
                type="textarea"
                :error-message="form.errors.description"
            />

            <TextInput
                v-if="form.type === 'speaker'"
                id="organization"
                v-model="form.organization"
                label="Organização"
                type="text"
                :error-message="form.errors.organization"
            />

            <details v-if="form.type !== 'admin'" class="self-stretch">
                <summary>Redes sociais</summary>

                <div class="mt-4 flex flex-col gap-4">
                    <TextInput
                        id="public_email"
                        v-model="form.public_email"
                        label="Public Email"
                        type="email"
                        autocomplete="email"
                        :error-message="form.errors.public_email"
                    />

                    <TextInput
                        id="facebook"
                        v-model="form.facebook"
                        label="Facebook"
                        type="url"
                        :error-message="form.errors.facebook"
                    />

                    <TextInput
                        id=".github"
                        v-model="form.github"
                        label="GitHub"
                        type="url"
                        :error-message="form.errors.github"
                    />

                    <TextInput
                        id="instagram"
                        v-model="form.instagram"
                        label="Instagram"
                        type="url"
                        :error-message="form.errors.instagram"
                    />

                    <TextInput
                        id="linkedin"
                        v-model="form.linkedin"
                        label="Linkedin"
                        type="url"
                        :error-message="form.errors.linkedin"
                    />

                    <TextInput
                        id="twitter"
                        v-model="form.twitter"
                        label="Twitter"
                        type="url"
                        :error-message="form.errors.twitter"
                    />

                    <TextInput
                        id="website"
                        v-model="form.website"
                        label="Website"
                        type="url"
                        :error-message="form.errors.website"
                    />
                </div>
            </details>

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
