<script setup lang="ts">
import type { User } from "@/Types/User";
import { ref } from "vue";

interface Props {
    item: User | undefined;
}

defineProps<Props>();

const cvInput = ref<HTMLInputElement | null>(null);

const selectCV = () => {
    cvInput.value?.click();
};

const uploadCV = () => {};
</script>

<template>
    <div class="flex-col">
        <div
            class="mx-32 mt-12 flex justify-between border border-solid border-black bg-2023-teal-dark p-3 font-bold text-white"
        >
            <div class="flex">
                <p>CV upload</p>
                <button
                    v-if="$page.props.auth.user.id == item?.id"
                    class="mx-2"
                    @click.prevent="selectCV"
                >
                    <v-icon name="io-share" fill="white" scale="1.3"></v-icon>
                </button>
                <input
                    ref="cvInput"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="uploadCV"
                />
            </div>
            <button>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 pl-1 text-2023-bg"
                    viewBox="0 0 512 512"
                >
                    <path
                        fill="none"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="48"
                        d="M112 184l180 160 180-160"
                    />
                </svg>
            </button>
        </div>
        <div
            class="mx-32 mb-12 mt-3 border border-2 border-solid border-black p-3 text-2023-red"
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
