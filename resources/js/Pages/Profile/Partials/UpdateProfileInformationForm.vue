<script setup lang="ts">
import { ref, computed } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import Modal from "@/Components/UI/Modal.vue";
import Card from "@/Components/UI/Card.vue";
import SocialIcon from "@/Components/UI/SocialIcon.vue";
import { Camera, Upload, Trash2, AlertCircle } from "@lucide/vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ProfileCard from "@/Components/Profile/ProfileCard.vue";
import TextInput from "@/Components/TextInput.vue";
import {
    type User,
    isCompany as checkIsCompany,
    isSpeaker as checkIsSpeaker,
    isAdmin as checkIsAdmin,
} from "@/Types/User";
import { route } from "ziggy-js";

interface Props {
    user: User | undefined;
}

const { user } = defineProps<Props>();

const isCompany = checkIsCompany(user);
const isSpeaker = checkIsSpeaker(user);
const isAdmin = checkIsAdmin(user);

const userTypeLabel = computed(() => {
    if (isAdmin) return "Administrador";
    if (isSpeaker) return "Orador";
    if (isCompany) return "Empresa";
    return "Participante";
});

// Profile Photo Editing State & Modal
const photoModalOpen = ref(false);
const photoInput = ref<HTMLInputElement | null>(null);
const photoPreview = ref<string | null>(null);
const photoError = ref<string | null>(null);
const isDeletingPhoto = ref(false);

const photoForm = useForm({
    _method: "PUT",
    name: user ? user.name : "",
    email: user ? user.email : "",
    photo: null as File | null,
});

const handlePhotoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    photoError.value = null;

    if (!file) return;

    if (!["image/jpeg", "image/png", "image/jpg"].includes(file.type)) {
        photoError.value =
            "Por favor seleciona uma imagem válida (JPG ou PNG).";
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        photoError.value = "A imagem não pode exceder 10 MB.";
        return;
    }

    photoForm.photo = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const cancelPhotoSelection = () => {
    photoPreview.value = null;
    photoForm.photo = null;
    photoError.value = null;
    if (photoInput.value) {
        photoInput.value.value = "";
    }
};

const closePhotoModal = () => {
    cancelPhotoSelection();
    photoModalOpen.value = false;
};

const savePhoto = () => {
    if (!photoForm.photo) return;

    photoForm.post(route("user-profile-information.update"), {
        errorBag: "updateProfileInformation",
        preserveScroll: true,
        onSuccess: () => {
            closePhotoModal();
        },
        onError: (errors) => {
            if (errors.photo) {
                photoError.value = errors.photo;
            }
        },
    });
};

const deletePhoto = () => {
    isDeletingPhoto.value = true;
    router.delete(route("current-user-photo.destroy"), {
        preserveScroll: true,
        onSuccess: () => {
            closePhotoModal();
        },
        onFinish: () => {
            isDeletingPhoto.value = false;
        },
    });
};

const form = useForm({
    _method: "PUT",
    name: user ? user.name : "",
    email: user ? user.email : "",
    type: (user?.usertype_type.split("\\").pop() ?? "").toLowerCase() as
        "participant" | "company" | "speaker" | "admin",
    title: isSpeaker ? (user?.usertype?.title ?? "") : "",
    description:
        isCompany || isSpeaker ? (user?.usertype?.description ?? "") : "",
    organization: isSpeaker ? (user?.usertype?.organization ?? "") : "",
    public_email: !isAdmin ? (user?.usertype?.social_media?.email ?? "") : "",
    facebook: !isAdmin ? (user?.usertype?.social_media?.facebook ?? "") : "",
    github: !isAdmin ? (user?.usertype?.social_media?.github ?? "") : "",
    instagram: !isAdmin ? (user?.usertype?.social_media?.instagram ?? "") : "",
    linkedin: !isAdmin ? (user?.usertype?.social_media?.linkedin ?? "") : "",
    twitter: !isAdmin ? (user?.usertype?.social_media?.twitter ?? "") : "",
    website: !isAdmin ? (user?.usertype?.social_media?.website ?? "") : "",
});

const verificationLinkSent = ref(false);

const updateProfileInformation = () => {
    form.post(route("user-profile-information.update"), {
        errorBag: "updateProfileInformation",
        preserveScroll: true,
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};
</script>

<template>
    <div class="space-y-6">
        <ProfileCard :user="user" :show-social-media="false">
            <template #actions>
                <PrimaryButton
                    type="button"
                    color="pill"
                    padding="px-3.5 py-2 sm:px-4 sm:py-2"
                    class="w-full sm:w-auto"
                    @click="photoModalOpen = true"
                >
                    <Camera :size="15" />
                    <span>Alterar fotografia</span>
                </PrimaryButton>
            </template>
        </ProfileCard>

        <Card as="section" :interactive="false" padding="p-6 sm:p-8">
            <template #header>
                <div class="space-y-1">
                    <h2
                        class="text-lg font-bold tracking-tight text-white sm:text-xl"
                    >
                        Informações Pessoais
                    </h2>
                    <p class="text-xs text-neutral-400 sm:text-sm">
                        Atualiza os dados da tua conta e informações de
                        contacto.
                    </p>
                </div>
            </template>

            <form
                id="profile-info-form"
                class="flex flex-col gap-5"
                @submit.prevent="updateProfileInformation"
            >
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <TextInput
                        id="name"
                        v-model="form.name"
                        label="Nome"
                        type="text"
                        autocomplete="name"
                        :error-message="form.errors.name"
                    />

                    <TextInput
                        id="email"
                        v-model="form.email"
                        label="Email"
                        type="email"
                        autocomplete="username"
                        :error-message="form.errors.email"
                    />
                </div>

                <div
                    v-if="
                        $page.props.jetstream.hasEmailVerification &&
                        user?.email_verified_at === null
                    "
                    class="rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4 text-xs text-amber-200"
                >
                    <p>
                        O teu endereço de e-mail não foi verificado.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="ml-1 font-semibold underline hover:text-white"
                            @click.prevent="sendEmailVerification"
                        >
                            Clica aqui para reenviar o e-mail de verificação.
                        </Link>
                    </p>

                    <div
                        v-show="verificationLinkSent"
                        class="mt-2 font-medium text-emerald-400"
                    >
                        Um novo link de verificação foi enviado para o teu
                        email.
                    </div>
                </div>

                <template v-if="form.type === 'speaker'">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <TextInput
                            id="title"
                            v-model="form.title"
                            label="Título"
                            type="text"
                            :error-message="form.errors.title"
                        />

                        <TextInput
                            id="organization"
                            v-model="form.organization"
                            label="Organização"
                            type="text"
                            :error-message="form.errors.organization"
                        />
                    </div>
                </template>

                <TextInput
                    v-if="form.type === 'company' || form.type === 'speaker'"
                    id="description"
                    v-model="form.description"
                    label="Descrição"
                    type="textarea"
                    :error-message="form.errors.description"
                />

                <template v-if="form.type !== 'admin'">
                    <div class="mt-2 border-t border-white/8 pt-5">
                        <h3 class="text-sm font-semibold text-white">
                            Redes Sociais & Links
                        </h3>
                        <p class="mt-0.5 mb-4 text-xs text-neutral-400">
                            Links públicos visíveis no teu cartão de perfil.
                        </p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <TextInput
                                id="public_email"
                                v-model="form.public_email"
                                label="Email público"
                                type="email"
                                placeholder="exemplo@dominio.com"
                                autocomplete="email"
                                :error-message="form.errors.public_email"
                            >
                                <template #leading>
                                    <SocialIcon
                                        platform="email"
                                        :size="16"
                                        class="shrink-0 text-neutral-400"
                                    />
                                </template>
                            </TextInput>

                            <TextInput
                                id="website"
                                v-model="form.website"
                                label="Website"
                                type="url"
                                placeholder="https://oseusite.com"
                                :error-message="form.errors.website"
                            >
                                <template #leading>
                                    <SocialIcon
                                        platform="website"
                                        :size="16"
                                        class="shrink-0 text-neutral-400"
                                    />
                                </template>
                            </TextInput>

                            <TextInput
                                id="github"
                                v-model="form.github"
                                label="GitHub"
                                type="url"
                                placeholder="https://github.com/utilizador"
                                :error-message="form.errors.github"
                            >
                                <template #leading>
                                    <SocialIcon
                                        platform="github"
                                        :size="16"
                                        class="shrink-0 text-neutral-400"
                                    />
                                </template>
                            </TextInput>

                            <TextInput
                                id="linkedin"
                                v-model="form.linkedin"
                                label="LinkedIn"
                                type="url"
                                placeholder="https://linkedin.com/in/utilizador"
                                :error-message="form.errors.linkedin"
                            >
                                <template #leading>
                                    <SocialIcon
                                        platform="linkedin"
                                        :size="16"
                                        class="shrink-0 text-neutral-400"
                                    />
                                </template>
                            </TextInput>

                            <TextInput
                                id="twitter"
                                v-model="form.twitter"
                                label="Twitter / X"
                                type="url"
                                placeholder="https://x.com/utilizador"
                                :error-message="form.errors.twitter"
                            >
                                <template #leading>
                                    <SocialIcon
                                        platform="twitter"
                                        :size="16"
                                        class="shrink-0 text-neutral-400"
                                    />
                                </template>
                            </TextInput>

                            <TextInput
                                id="instagram"
                                v-model="form.instagram"
                                label="Instagram"
                                type="url"
                                placeholder="https://instagram.com/utilizador"
                                :error-message="form.errors.instagram"
                            >
                                <template #leading>
                                    <SocialIcon
                                        platform="instagram"
                                        :size="16"
                                        class="shrink-0 text-neutral-400"
                                    />
                                </template>
                            </TextInput>

                            <TextInput
                                id="facebook"
                                v-model="form.facebook"
                                label="Facebook"
                                type="url"
                                placeholder="https://facebook.com/utilizador"
                                :error-message="form.errors.facebook"
                            >
                                <template #leading>
                                    <SocialIcon
                                        platform="facebook"
                                        :size="16"
                                        class="shrink-0 text-neutral-400"
                                    />
                                </template>
                            </TextInput>
                        </div>
                    </div>
                </template>
            </form>

            <template #footer>
                <div class="flex w-full items-center justify-end gap-3">
                    <span
                        v-show="form.recentlySuccessful"
                        class="text-xs font-medium text-emerald-400"
                    >
                        Guardado com sucesso.
                    </span>

                    <PrimaryButton
                        type="submit"
                        form="profile-info-form"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Guardar alterações
                    </PrimaryButton>
                </div>
            </template>
        </Card>

        <Modal
            v-model="photoModalOpen"
            max-width="md"
            title="Fotografia de Perfil"
            description="Personaliza a tua fotografia visível no teu perfil."
            @close="closePhotoModal"
        >
            <div class="flex flex-col items-center justify-center py-2">
                <div
                    class="h-32 w-32 shrink-0 overflow-hidden rounded-full bg-neutral-900 ring-4 ring-white/10"
                >
                    <img
                        :src="photoPreview ?? user?.profile_photo_url"
                        :alt="user?.name"
                        class="h-full w-full object-cover"
                    />
                </div>
            </div>

            <input
                ref="photoInput"
                type="file"
                class="hidden"
                accept="image/jpeg,image/png,image/jpg"
                @change="handlePhotoChange"
            />

            <div class="mt-3 flex flex-col items-center gap-1.5 text-center">
                <PrimaryButton
                    type="button"
                    color="pill"
                    padding="px-5 py-2"
                    @click="photoInput?.click()"
                >
                    <Upload :size="14" />
                    <span>{{
                        photoPreview
                            ? "Escolher outro ficheiro"
                            : "Carregar nova foto"
                    }}</span>
                </PrimaryButton>
                <span class="text-[11px] text-neutral-500">
                    Formatos aceites: JPG ou PNG (máx. 10MB)
                </span>
            </div>

            <div
                v-if="photoError || photoForm.errors.photo"
                class="mt-3 flex items-center gap-2 rounded-xl border border-red-500/20 bg-red-500/10 p-2.5 text-xs text-red-400"
            >
                <AlertCircle :size="15" class="shrink-0" />
                <span>{{ photoError || photoForm.errors.photo }}</span>
            </div>

            <template #footer>
                <div class="flex w-full items-center justify-between">
                    <PrimaryButton
                        v-if="user?.profile_photo_path && !photoPreview"
                        type="button"
                        color="danger"
                        padding="px-3.5 py-1.5"
                        :disabled="isDeletingPhoto"
                        @click="deletePhoto"
                    >
                        <Trash2 :size="13" />
                        <span>{{
                            isDeletingPhoto ? "A remover..." : "Remover foto"
                        }}</span>
                    </PrimaryButton>
                    <div v-else />

                    <div class="flex items-center gap-2">
                        <PrimaryButton
                            type="button"
                            color="pill"
                            padding="px-4 py-1.5"
                            @click="closePhotoModal"
                        >
                            {{ photoPreview ? "Cancelar" : "Fechar" }}
                        </PrimaryButton>

                        <PrimaryButton
                            v-if="photoPreview"
                            type="button"
                            color="pill"
                            padding="px-4 py-1.5"
                            :disabled="photoForm.processing"
                            @click="savePhoto"
                        >
                            {{
                                photoForm.processing
                                    ? "A guardar..."
                                    : "Guardar fotografia"
                            }}
                        </PrimaryButton>
                    </div>
                </div>
            </template>
        </Modal>
    </div>
</template>
