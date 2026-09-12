<script setup lang="ts">
import ImageInput from "@/Components/Form/ImageInput.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import {
    type User,
    isCompany as checkIsCompany,
    isSpeaker as checkIsSpeaker,
    isAdmin as checkIsAdmin,
} from "@/Types/User";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    item: User;
}

const { item: user } = defineProps<Props>();

const isCompany = checkIsCompany(user);
const isSpeaker = checkIsSpeaker(user);
const isAdmin = checkIsAdmin(user);

const form = useForm({
    _method: "PUT",
    name: user.name,
    email: user.email,
    type: (user.usertype_type.split("\\").pop() ?? "").toLowerCase() as
        | "participant"
        | "company"
        | "speaker"
        | "admin",
    title: isSpeaker ? (user.usertype?.title ?? "") : "",
    display_name: isSpeaker ? (user.usertype?.display_name ?? "") : "",
    description:
        isCompany || isSpeaker ? (user.usertype?.description ?? "") : "",
    organization: isSpeaker ? (user.usertype?.organization ?? "") : "",
    public_email: !isAdmin ? (user?.usertype?.social_media?.email ?? "") : "",
    facebook: !isAdmin ? (user?.usertype?.social_media?.facebook ?? "") : "",
    github: !isAdmin ? (user?.usertype?.social_media?.github ?? "") : "",
    instagram: !isAdmin ? (user?.usertype?.social_media?.instagram ?? "") : "",
    linkedin: !isAdmin ? (user?.usertype?.social_media?.linkedin ?? "") : "",
    twitter: !isAdmin ? (user?.usertype?.social_media?.twitter ?? "") : "",
    website: !isAdmin ? (user?.usertype?.social_media?.website ?? "") : "",
    photo: null as File | null,
});

const submit = () => {
    form.post(route("admin.users.update", { user: user.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar utilizador"
        name="users"
        :processing="form.processing"
        max-width="2xl"
        @submit="submit"
    >
        <ImageInput
            id="photo"
            v-model="form.photo"
            :initial-preview="user.profile_photo_url"
            label="Foto de perfil"
            class="self-stretch"
            :error-message="form.errors.photo"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
        </div>

        <TextInput
            v-model="form.type"
            type="select"
            label="Tipo de utilizador"
            required
            :error-message="form.errors.type"
        >
            <option value="participant">Participante</option>
            <option value="company">Empresa</option>
            <option value="speaker">Orador</option>
            <option value="admin">Administrador</option>
        </TextInput>

        <div
            v-if="form.type === 'speaker'"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2"
        >
            <TextInput
                id="title"
                v-model="form.title"
                label="Título / Cargo"
                type="text"
                :error-message="form.errors.title"
            />

            <TextInput
                id="displayName"
                v-model="form.display_name"
                label="Nome a apresentar"
                type="text"
                :error-message="form.errors.display_name"
            />

            <TextInput
                id="organization"
                v-model="form.organization"
                label="Organização / Empresa"
                type="text"
                class="sm:col-span-2"
                :error-message="form.errors.organization"
            />
        </div>

        <TextInput
            v-if="form.type === 'company' || form.type === 'speaker'"
            id="description"
            v-model="form.description"
            label="Descrição / Biografia"
            type="textarea"
            :error-message="form.errors.description"
        />

        <details
            v-if="form.type && form.type !== 'admin'"
            class="group rounded-2xl border border-white/10 bg-white/[0.02] p-4 transition-colors hover:border-white/20"
        >
            <summary
                class="cursor-pointer font-mono text-xs font-semibold uppercase tracking-wider text-neutral-400 transition-colors group-open:text-white"
            >
                Redes sociais & Contactos públicos
            </summary>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <TextInput
                    id="public_email"
                    v-model="form.public_email"
                    label="Email Público"
                    type="email"
                    autocomplete="email"
                    :error-message="form.errors.public_email"
                />

                <TextInput
                    id="website"
                    v-model="form.website"
                    label="Website"
                    type="url"
                    :error-message="form.errors.website"
                />

                <TextInput
                    id="linkedin"
                    v-model="form.linkedin"
                    label="LinkedIn"
                    type="url"
                    :error-message="form.errors.linkedin"
                />

                <TextInput
                    id="github"
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
                    id="facebook"
                    v-model="form.facebook"
                    label="Facebook"
                    type="url"
                    :error-message="form.errors.facebook"
                />

                <TextInput
                    id="twitter"
                    v-model="form.twitter"
                    label="Twitter / X"
                    type="url"
                    class="sm:col-span-2"
                    :error-message="form.errors.twitter"
                />
            </div>
        </details>
    </CRUDModal>
</template>
