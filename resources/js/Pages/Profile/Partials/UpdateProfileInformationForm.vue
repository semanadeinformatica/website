<script setup lang="ts">
import { ref } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import type { User } from "@/Types/User";
import route from "ziggy-js";

interface Props {
    user: User;
}

const {user: user} = defineProps<Props>();

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
                    label="Name"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="name"
                    :error-message="form.errors.name"
                />
                <TextInput
                    id="email"
                    v-model="form.email"
                    label="Email"
                    type="email"
                    class="mt-1 block w-full"
                    autocomplete="username"
                    :error-message="form.errors.email"
                />

                <div
                    v-if="
                        $page.props.jetstream.hasEmailVerification &&
                        user.email_verified_at === null
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

            <details v-if="form.type !== 'admin'" class="self-stretch list-none">
                <summary class="text-2023-teal-dark">Redes sociais</summary>

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
                        label="GitHub"
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
