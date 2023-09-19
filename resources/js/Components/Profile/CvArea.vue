<script setup lang="ts">
import type { User } from "@/Types/User";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import route from "ziggy-js";

interface Props {
    item: User | undefined;
}

const props = defineProps<Props>();

let previewOpen = ref(false);

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
    console.log(cvInput.value?.files?.[0]);
    if (cv) {
        form.cv = cvInput.value?.files?.[0] ?? null;
        form.post(route("current-user-cv.update"), {
            preserveScroll: true,
            onSuccess: () => clearCVFileInput,
        });
    }
    clearCVFileInput();
};

const clearCVFileInput = () => {
    if (cvInput.value?.value) {
        cvInput.value.value = "";
    }
};
</script>

<template>
    <div class="flex-col">
        <div
            class="mx-32 mt-12 flex justify-between border-solid border-black bg-2023-teal-dark p-3 font-bold text-white"
            :class="[previewOpen ? 'border-x border-t' : 'border']"
        >
            <div class="flex items-center">
                <p>CV upload</p>
                <button
                    v-if="$page.props.auth.user.id == item?.id"
                    class="mx-3"
                    @click.prevent="selectCV"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-6 text-white"
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
                class="p-2"
                :class="[previewOpen ? 'rotate-180' : '']"
                @click="togglePreview"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 text-white"
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
            class="mx-36 h-fit items-center justify-center border-x border-b border-solid border-black bg-2023-red-dark p-6 font-bold text-white"
            :class="[previewOpen ? 'flex' : 'hidden']"
        >
            <vue-pdf-embed
                v-if="
                    item?.usertype_type === 'App\\Models\\Participant' &&
                    item.usertype?.cv_path
                "
                width="900"
                :source="item.usertype?.cv_url"
            ></vue-pdf-embed>
            <p
                v-if="
                    item?.usertype_type === 'App\\Models\\Participant' &&
                    !item.usertype?.cv_path
                "
            >
                Nenhum CV disponível
            </p>
        </div>
        <div
            class="mx-32 mb-12 mt-3 border-2 border-solid border-black p-3 text-2023-red"
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
