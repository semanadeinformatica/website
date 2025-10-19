<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import type Quest from "@/Types/Quest";
import { useForm, usePage } from "@inertiajs/vue3";
import { OhVueIcon } from "oh-vue-icons";
import { computed, ref, watch } from "vue";
import { QrcodeStream } from "vue-qrcode-reader";
import route from "ziggy-js";

const props = defineProps<{
    selectedQuest?: Quest;
    quests: Quest[];
}>();

const error = ref("");
const scanning = ref(false);

const selectedQuest = computed(() => props.selectedQuest);

const form = useForm({
    quest_code: "",
    quest: selectedQuest.value?.id.toString() ?? "",
});

const submit = () =>
    form.post(route("quest.give", { quest: form.quest }), {
        onSuccess: () => (form.quest_code = ""),
        preserveState: true,
        preserveScroll: true,
    });

const errorMap = {
    NotAllowedError: "Sem permissão para aceder à câmara",
    NotFoundError: "Câmara não encontrada",
    NotSupportedError: "Câmara não suportada",
    NotReadableError: "Câmara já em uso",
    OverconstrainedError: "Câmaras não é adequadas",
    StreamApiNotSupportedError: "Stream API não suportada",
    InsecureContextError: "Acesso à câmara não permitido em contexto inseguro",
};

const onError = (err: Error) => {
    if (err.name in errorMap)
        error.value = errorMap[err.name as keyof typeof errorMap];
    else error.value = err.message;
};

// @ts-expect-error: firstDetectedCode comes from the qrcode-reader package which has no types
const onDetect = async ([firstDetectedCode]) => {
    form.quest_code = firstDetectedCode.rawValue;

    if (form.quest && form.quest_code) submit();
};


const showScanResult = ref(false);

const message = computed(() => usePage().props.jetstream.flash?.banner || "");
const id = computed(() => usePage().props.jetstream.flash?.bannerId || "");


watch(id, async () => {
    showScanResult.value = true;
})

</script>

<template>
    <AppLayout title="Ler QR Code">
        <div
            class="flex w-full flex-col items-center gap-5 pt-10 text-text-color"
        >
            <h2
                class="w-fit border border-black bg-2025-blue px-3 py-2 text-2xl font-bold text-text-color shadow-md shadow-black/80 rounded-lg"
            >
                Instruções
            </h2>
            <ul class="flex w-fit flex-col items-center px-4 text-center">
                <li>
                    No perfil do participante, encontrar o ícone de QR Code
                    <span class="text-2025-blue"
                        ><OhVueIcon name="io-qr-code" /></span
                    >, clicar no ícone e dar scan ao código.
                </li>
                <li>
                    Alternativamente é possível introduzir o código de 10
                    dígitos situado no mesmo local.
                </li>
                <li>
                    <span class="font-bold text-2025-blue">IMPORTANTE</span> -
                    Antes de scannar verificar a tarefa selecionada no dropdown
                    em baixo, o dia deve corresponder ao atual
                </li>
            </ul>
            <div
                :hidden="!scanning"
                class="h-80 w-80 border border-black shadow-lg rounded-lg overflow-hidden"
            >
                <QrcodeStream
                    @camera-on="scanning = true"
                    @camera-off="scanning = false"
                    @detect="onDetect"
                    @error="onError"
                ></QrcodeStream>
            </div>
            <p class="text-center text-red-600">{{ error }}</p>
            <form class="contents" @submit.prevent="submit">
                <div class="w-80">
                    <TextInput
                        id="code"
                        v-model="form.quest_code"
                        label="Código"
                        type="text"
                        required
                        class="font-mono text-2025-blue-dark"
                        :error-message="form.errors.quest_code"
                    />
                </div>

                <div class="w-80">
                    <TextInput
                        class="text-2025-blue-dark"
                        id="quest"
                        v-model="form.quest"
                        label="Quest"
                        type="select"
                        :disabled="selectedQuest !== undefined"
                        required
                        :error-message="form.errors.quest"
                    >
                        <option
                            v-for="quest in quests"
                            :key="quest.id"
                            :value="quest.id"
                        >
                            {{ quest.name }}
                        </option>
                    </TextInput>
                </div>

                <PrimaryButton type="submit">Confirmar</PrimaryButton>
                <p v-if="showScanResult">{{ message }}</p>
            </form>
        </div>
    </AppLayout>
</template>
