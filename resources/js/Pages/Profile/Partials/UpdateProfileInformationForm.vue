<script setup lang="ts">
import { ref } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import type { User } from "@/Types/User";
import route from "ziggy-js";

interface Props {
    user: User;
}

const props = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    name: props.user.name,
    email: props.user.email,
    photo: null as File | null,
});

const verificationLinkSent = ref(false);
const photoPreview = ref<string | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);

const updateProfileInformation = () => {
    if (photoInput.value) form.photo = photoInput.value.files?.[0] ?? null;

    if (CVInput.value) {
        form.cv = CVInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearInputs(),
    });
}

const downloadCV = (user) => {
    fetch(route('file.download', { 
        url: user.cv_url,
        storageDisk: 'public' })) //hardcoded for now
    .then((res) => res.blob())
    .then((blob) => {
      const file = window.URL.createObjectURL(blob)
      window.open(file, '_blank')
    });
}

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value?.click();
};

const selectNewCV = () => {
    CVInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value?.files?.[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = (e.target?.result as string) ?? null;
    };

    reader.readAsDataURL(photo);
};

const updateCVPreview = () => {
    const cv = CVInput.value.files[0];

    if (! cv) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        CVPreview.value = e.target.result;
    };

    reader.readAsDataURL(cv);
};

const deletePhoto = () => {
    router.delete(route("current-user-photo.destroy"), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const deleteCV = () => {
    router.delete(route('current-user-cv.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearCVInput();
        },
    });
};

const clearInputs = () => {
    clearPhotoFileInput();
    clearCVInput();
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = "";
    }
};

const clearCVInput = () => {
    if (CVInput.value?.value) {
        CVInput.value.value = null;
    }
    CVPreview.value = null;
};

</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title> Profile Information </template>

        <template #description>
            Update your account's profile information and email address.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div
                v-if="$page.props.jetstream.managesProfilePhotos"
                class="col-span-6 sm:col-span-4"
            >
                <!-- Profile Photo File Input -->
                <input
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                />

                <InputLabel for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div v-show="!photoPreview" class="mt-2">
                    <img
                        :src="user.profile_photo_url"
                        :alt="user.name"
                        class="h-20 w-20 rounded-full object-cover"
                    />
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block h-20 w-20 rounded-full bg-cover bg-center bg-no-repeat"
                        :style="
                            'background-image: url(\'' + photoPreview + '\');'
                        "
                    />
                </div>

                <SecondaryButton
                    class="mr-2 mt-2"
                    type="button"
                    @click.prevent="selectNewPhoto"
                >
                    Select A New Photo
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.profile_photo_path"
                    type="button"
                    class="mt-2"
                    @click.prevent="deletePhoto"
                >
                    Remove Photo
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <!-- CV Input -->
                <input
                    ref="CVInput"
                    type="file"
                    class="hidden"
                    @change="updateCVPreview"
                >

                <InputLabel for="cv" value="CV" />

                <!-- Current CV -->
                <SecondaryButton v-if="user.cv_path" class="mt-2 mr-2" type="button" @click.prevent="downloadCV(user)">
                    Preview CV
                </SecondaryButton>

                <!-- New CV Preview -->
                <div v-show="CVPreview" class="mt-2">
                    <a
                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center bg-blue-500 py-6 text-center"
                        target="_blank"
                        :href=CVPreview
                    >
                    Preview
                    </a>

                </div>

                <SecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewCV">
                    Select A New CV
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.cv_path"
                    type="button"
                    class="mt-2"
                    @click.prevent="deleteCV"
                >
                    Remove CV
                </SecondaryButton>

                <InputError :message="form.errors.cv" class="mt-2" />
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-2" />

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
@/Types/User
