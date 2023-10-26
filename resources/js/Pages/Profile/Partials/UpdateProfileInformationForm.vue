<script setup lang="ts">
import { ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import {
    type User,
    isCompany as checkIsCompany,
    isSpeaker as checkIsSpeaker,
    isAdmin as checkIsAdmin,
} from "@/Types/User";
import route from "ziggy-js";

interface Props {
    user: User | undefined;
}

const { user: user } = defineProps<Props>();

const isCompany = checkIsCompany(user);
const isSpeaker = checkIsSpeaker(user);
const isAdmin = checkIsAdmin(user);

const form = useForm({
    _method: "PUT",
    name: user ? user.name : "",
    email: user ? user.email : "",
    type: (user?.usertype_type.split("\\").pop() ?? "").toLowerCase() as
        | "participant"
        | "company"
        | "speaker"
        | "admin",
    title: isSpeaker ? user?.usertype?.title ?? "" : "",
    description:
        isCompany || isSpeaker ? user?.usertype?.description ?? "" : "",
    organization: isSpeaker ? user?.usertype?.organization ?? "" : "",
    public_email: !isAdmin ? user?.usertype?.social_media?.email ?? "" : "",
    facebook: !isAdmin ? user?.usertype?.social_media?.facebook ?? "" : "",
    github: !isAdmin ? user?.usertype?.social_media?.github ?? "" : "",
    instagram: !isAdmin ? user?.usertype?.social_media?.instagram ?? "" : "",
    linkedin: !isAdmin ? user?.usertype?.social_media?.linkedin ?? "" : "",
    twitter: !isAdmin ? user?.usertype?.social_media?.twitter ?? "" : "",
    website: !isAdmin ? user?.usertype?.social_media?.website ?? "" : "",
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
    <FormSection @submitted="updateProfileInformation">
        <template #title> Profile Information </template>

        <template #description>
            Update your account's profile information and email address.
        </template>

        <template #form>
            <!-- Email -->
            <div class="flex flex-col gap-5">
                <!-- Name -->
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

                <div
                    v-if="
                        $page.props.jetstream.hasEmailVerification &&
                        user?.email_verified_at === null
                    "
                >
                    <p class="mt-2 text-sm dark:text-white">
                        Your email address is unverified.

                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                            @click.prevent="sendEmailVerification"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div
                        v-show="verificationLinkSent"
                        class="mt-2 text-sm font-medium text-green-600 dark:text-green-400"
                    >
                        A new verification link has been sent to your email
                        address.
                    </div>
                </div>

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

                <template v-if="form.type !== 'admin'">
                    <TextInput
                        id="public_email"
                        v-model="form.public_email"
                        label="Email público"
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
                </template>
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </ActionMessage>

            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Save
            </PrimaryButton>
        </template>
    </FormSection>
</template>
