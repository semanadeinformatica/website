<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import { ref } from "vue";
import { QrcodeStream } from "vue-qrcode-reader";

const result = ref("");
const error = ref("");

// @ts-expect-error: err comes from the qrcode-reader package which has no types
const onError = (err) => {
    error.value = `[${err.name}]: `;

    if (err.name === "NotAllowedError") {
        error.value += "you need to grant camera access permission";
    } else if (err.name === "NotFoundError") {
        error.value += "no camera on this device";
    } else if (err.name === "NotSupportedError") {
        error.value += "secure context required (HTTPS, localhost)";
    } else if (err.name === "NotReadableError") {
        error.value += "is the camera already in use?";
    } else if (err.name === "OverconstrainedError") {
        error.value += "installed cameras are not suitable";
    } else if (err.name === "StreamApiNotSupportedError") {
        error.value += "Stream API is not supported in this browser";
    } else if (err.name === "InsecureContextError") {
        error.value +=
            "Camera access is only permitted in secure context. Use HTTPS or localhost rather than HTTP.";
    } else {
        error.value += err.message;
    }
};

// @ts-expect-error: firstDetectedCode comes from the qrcode-reader package which has no types
const onDetect = async ([firstDetectedCode]) => {
    console.log(firstDetectedCode);

    result.value = firstDetectedCode.rawValue;
};

const form = useForm({
    qr_code: "",
});

const submit = () => {
    // TODO: BACKEND
    // form.post(route());
};
</script>

<template>
    <AppLayout title="Ler QR Code">
        <div
            class="flex w-full flex-col items-center gap-5 pt-10 text-2023-teal-dark"
        >
            <h2
                class="w-fit border border-black bg-2023-teal px-3 py-2 text-2xl font-bold text-2023-bg shadow shadow-2023-red"
            >
                Instruções
            </h2>
            <ul class="flex w-fit flex-col items-center px-4 text-center">
                <li>
                    No perfil do participante, encontrar o ícone de QR Code
                    <span class="text-2023-red"
                        ><OhVueIcon name="io-qr-code" /></span
                    >, clickar no ícone e scannar o código.
                </li>
                <li>
                    Alternativamente é possível introduzir o código de 10
                    dígitos situado no mesmo local.
                </li>
                <li>
                    <span class="font-bold text-2023-red">IMPORTANTE</span> -
                    Antes de scannar verificar o Quest selecionado no dropdown
                    em baixo, o dia deve corresponder ao atual
                </li>
            </ul>
            <div
                class="h-80 w-80 border border-black shadow-lg shadow-2023-red"
            >
                <QrcodeStream
                    @detect="onDetect"
                    @error="onError"
                ></QrcodeStream>
            </div>
            <!-- WARN: FOR TESTING PURPOSES RESULT IS SHOWN HERE -->
            <p class="text-center text-2023-teal-dark">{{ result }}</p>
            <p class="text-center text-red-600">{{ error }}</p>
            <form class="contents" @submit.prevent="submit">
                <div class="w-80">
                    <TextInput
                        id="code"
                        v-model="form.qr_code"
                        label="Código"
                        type="text"
                        required
                    />
                </div>
                <PrimaryButton type="submit">Confirmar</PrimaryButton>
            </form>
        </div>
    </AppLayout>
</template>
