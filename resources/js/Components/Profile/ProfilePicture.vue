<script setup lang="ts">
import { ref } from "vue";
import type { User } from "@/Types/User";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: User | undefined;
}

const props = defineProps<Props>();

const form = useForm({
    _method: "PUT",
    name: props.item?.name,
    email: props.item?.email,
    photo: null as File | null,
});

const photoInput = ref<HTMLInputElement | null>(null);

const selectNewPhoto = () => {
    console.log(props.item?.profile_photo_url);
    photoInput.value?.click();
};

const uploadNewPhoto = () => {
    const photo = photoInput.value?.files?.[0];
    if (photo) {
        form.photo = photoInput.value?.files?.[0] ?? null
        form.post(route('user-profile-information.update'), {
            errorBag: "updateProfileInformation",
            preserveScroll: true,
            onSuccess: () => clearPhotoFileInput,
        });
    }
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = "";
    }
};
</script>

<template>
    <div class="flex items-end">
        <div
            class="h-48 w-48 overflow-hidden rounded-full border-4 border-solid border-2023-teal"
        >
            <img
                :src="item?.profile_photo_url"
                :alt="item?.name"
                class="h-full w-full object-cover"
            />
        </div>
        <input ref="photoInput" type="file" class="hidden" accept="image/*" @change="uploadNewPhoto" />
        <button v-if="$page.props.auth.user.id == item?.id" @click.prevent="selectNewPhoto">
            <v-icon name="io-share" fill="2023-teal" scale="1.3"></v-icon>
        </button>
    </div>
</template>
