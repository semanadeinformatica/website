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
        | "admin",
    description:
        user.usertype_type === "App\\Models\\Company"
            ? user.usertype?.description ?? ""
            : "",
    social_media: {
        email:
            user.usertype_type !== "App\\Models\\Admin"
                ? user.usertype?.social_media?.email ?? ""
                : "",
        facebook:
            user.usertype_type !== "App\\Models\\Admin"
                ? user.usertype?.social_media?.facebook ?? ""
                : "",
        github:
            user.usertype_type !== "App\\Models\\Admin"
                ? user.usertype?.social_media?.github ?? ""
                : "",
        instagram:
            user.usertype_type !== "App\\Models\\Admin"
                ? user.usertype?.social_media?.instagram ?? ""
                : "",
        linkedin:
            user.usertype_type !== "App\\Models\\Admin"
                ? user.usertype?.social_media?.linkedin ?? ""
                : "",
        twitter:
            user.usertype_type !== "App\\Models\\Admin"
                ? user.usertype?.social_media?.twitter ?? ""
                : "",
        website:
            user.usertype_type !== "App\\Models\\Admin"
                ? user.usertype?.social_media?.website ?? ""
                : "",
    },
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
                v-model="form.photo"
                :initial-preview="item.profile_photo_url"
                class="self-stretch"
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

            <select v-model="form.type" required class="self-stretch" disabled>
                <option value="" disabled selected hidden>
                    Tipo de utilizador
                </option>
                <option value="participant">Participante</option>
                <option value="company">Empresa</option>
                <option value="admin">Administrador</option>
            </select>

            <TextInput
                v-if="form.type === 'company'"
                id="description"
                v-model="form.description"
                label="Descrição"
                type="text"
                required
                :error-message="form.errors.description"
            />

            <details v-if="form.type !== 'admin'" class="self-stretch">
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

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
