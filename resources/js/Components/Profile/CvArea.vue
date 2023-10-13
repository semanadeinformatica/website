<script setup lang="ts">
import { type User, isCompany, isParticipant } from "@/Types/User";
import { useForm, usePage } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import { ref } from "vue";
import route from "ziggy-js";

interface Props {
    item: User | undefined;
}

const props = defineProps<Props>();

const page = usePage();

let previewOpen = ref(isCompany(page.props.auth.user) ?? false);

const form = useForm({
    _method: "PUT",
    name: props.item?.name,
    email: props.item?.email,
    cv: null as File | null,
});

const cvInput = ref<HTMLInputElement | null>(null);

const selectCV = () => {
    cvInput.value?.click();
};

const togglePreview = () => {
    previewOpen.value = !previewOpen.value;
};

const uploadCV = () => {
    const cv = cvInput.value?.files?.[0];
    if (cv) {
        form.cv = cvInput.value?.files?.[0] ?? null;
        form.post(route("current-user-cv.update"), {
            preserveScroll: true,
            onSuccess: () => clearCVFileInput(),
        });
    }
};

const clearCVFileInput = () => {
    if (cvInput.value?.value) {
        cvInput.value.value = "";
    }
};
</script>

<template>
    <div class="w-full flex-col">
        <div
            class="mt-12 flex justify-between border-solid border-black bg-2023-teal-dark p-3 px-8 font-bold text-2023-bg md:mx-32"
            :class="[previewOpen ? 'border-x border-t' : 'border']"
        >
            <div class="flex items-center">
                <p>CV</p>
                <button
                    v-if="$page.props.auth.user?.id == item?.id"
                    class="mx-3"
                    @click.prevent="selectCV"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-6 text-2023-bg"
                        viewBox="0 0 512 512"
                    >
                        <path
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="36"
                            d="M56 262 v150 h400 M456 262 v150 M256 312 v-250 l-80 80 M256 62 l80 80"
                        />
                    </svg>
                </button>
                <input
                    ref="cvInput"
                    type="file"
                    class="hidden"
                    accept="application/pdf"
                    @change="uploadCV"
                />
            </div>
            <button
                :class="[previewOpen ? 'rotate-180' : '']"
                @click="togglePreview"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 text-2023-bg"
                    viewBox="0 0 512 512"
                >
                    <path
                        fill="none"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="48"
                        d="M70 184l180 160 180-160"
                    />
                </svg>
            </button>
        </div>
        <div
            class="mx-36 h-fit items-center justify-center border-x border-b border-solid border-black bg-2023-red-dark p-6 font-bold text-2023-bg max-md:mx-2"
            :class="[previewOpen ? 'flex' : 'hidden']"
        >
            <object
                v-if="isParticipant(item) && item.usertype?.cv_path"
                :data="item.usertype?.cv_url + '#toolbar&view=FitH'"
                width="100%"
                type="application/pdf"
                style="aspect-ratio: 1 / 1.41"
                class="max-md:hidden"
            ></object>
            <a
                v-if="isParticipant(item) && item.usertype?.cv_path"
                class="md:hidden"
                target="_blank"
                :href="item.usertype?.cv_url"
            >
                <div class="flex text-2023-bg">
                    <p class="mr-1">CV</p>
                    <OhVueIcon name="io-open" scale="1.3"></OhVueIcon>
                </div>
            </a>
            <p v-if="isParticipant(item) && !item.usertype?.cv_path">
                Nenhum CV disponível
            </p>
        </div>
        <div
            v-if="
                isParticipant(item) &&
                !item?.usertype?.cv_path &&
                $page.props.auth.user?.id == item.id
            "
            class="mb-12 mt-3 border-2 border-solid border-black p-3 text-2023-red md:mx-32"
        >
            <p>
                <span class="font-bold">Porque é que o deves fazer? </span>
                <span
                    >Esta é uma excelente oportunidade para te dares a conhecer
                    às empresas que participam na SINF!</span
                >
            </p>
            <p>
                No fim do evento o teu CV será dado a estas empresas para que
                possam ficar com os teus dados e, possivelmente, manter o
                contacto contigo.
            </p>
        </div>
    </div>
</template>
