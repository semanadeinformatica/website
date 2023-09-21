<script setup lang="ts">
import type { User } from "@/Types/User";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const options = ref({
    modelValue: false,
});

interface Props {
    item: User | undefined;
}

const { item: user } = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    name: user?.name ? user.name : "",
    email: user?.email ? user.email : "",
    description:
        user?.usertype_type === "App\\Models\\Company"
            ? user.usertype?.description ?? ""
            : "",
    type: (user?.usertype_type.split("\\").pop() ?? "").toLowerCase() as
        | "participant"
        | "company"
        | "admin",
    social_media: {
        email:
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
    },
});

const submit = () => {
    // change this
};

const socials = {
    facebook: {
        icon: "io-logo-facebook",
        color: "bg-2023-red",
    },
    linkedin: {
        icon: "io-logo-linkedin",
        color: "bg-2023-red-dark",
    },
    github: {
        icon: "io-logo-github",
        color: "bg-2023-red",
    },
    twitter: {
        icon: "io-logo-twitter",
        color: "bg-2023-red-dark",
    },
    instagram: {
        icon: "io-logo-instagram",
        color: "bg-2023-red",
    },
    website: {
        icon: "io-logo-globe",
        color: "bg-2023-red-dark",
    },
};
</script>

<template>
    <div
        class="m-4 flex justify-between border border-solid border-black bg-2023-bg p-3 text-2023-teal shadow-xl shadow-2023-red-dark max-md:m-2 md:min-w-[30vw]"
    >
        <div class="flex-col">
            <div>
                <p class="font-bold">{{ item?.name }}</p>
                <p>
                    {{ item?.email }}
                </p>
            </div>
            <div class="mt-10 flex">
                <template v-for="(social, key) in socials" :key="social">
                    <p
                        v-if="
                            item?.usertype_type !== 'App\\Models\\Admin' &&
                            item?.usertype?.social_media?.[key]
                        "
                    >
                        <a
                            class="flex w-fit rounded-full p-2.5"
                            target="_blank"
                            :href="item?.usertype?.social_media?.[key]"
                        >
                            <v-icon
                                :name="social.icon"
                                :fill="social.color"
                                scale="1.3"
                            ></v-icon>
                        </a>
                    </p>
                </template>
            </div>
        </div>
        <button
            v-if="$page.props.auth.user.id == item?.id"
            class="self-start"
            @click="options.modelValue = !options.modelValue"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-8 text-2023-teal"
                viewBox="0 0 512 512"
            >
                <path
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="36"
                    d="M384 224v184a40 40 0 01-40 40H104a40 40 0 01-40-40V168a40 40 0 0140-40h167.48"
                />
                <path
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="24"
                    d="M459.94 53.25a16.06 16.06 0 00-23.22-.56L424.35 65a8 8 0 000 11.31l11.34 11.32a8 8 0 0011.34 0l12.06-12c6.1-6.09 6.67-16.01.85-22.38zM399.34 90L218.82 270.2a9 9 0 00-2.31 3.93L208.16 299a3.91 3.91 0 004.86 4.86l24.85-8.35a9 9 0 003.93-2.31L422 112.66a9 9 0 000-12.66l-9.95-10a9 9 0 00-12.71 0z"
                />
            </svg>
        </button>
        <VueFinalModal
            v-model="options.modelValue"
            class="flex items-center justify-center"
            content-class="max-w-xl mx-4 p-4 bg-2023-bg border border-black border-solid flex relative justify-center flex-col"
        >
            <div
                class="relative m-6 flex flex-col items-center gap-8 border border-black p-8 pt-14 sm:max-w-lg"
            >
                <h2
                    class="absolute top-0 z-10 inline-block -translate-y-2/3 border border-black bg-2023-bg px-6 py-3 text-2xl font-bold text-2023-teal-dark shadow-xl shadow-2023-orange"
                >
                    Edita as tuas informações
                </h2>

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
                        id="email"
                        v-model="form.email"
                        label="Email"
                        type="email"
                        required
                        autocomplete="email"
                        :error-message="form.errors.email"
                    />

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
            </div>
        </VueFinalModal>
    </div>
</template>
