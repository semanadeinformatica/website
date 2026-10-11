<script setup lang="ts">
import { ref } from "vue";
import { Upload } from "@lucide/vue";

interface Props {
    id: string;
    modelValue?: File | null;
    label?: string;
    errorMessage?: string;
}

defineProps<Props>();

const emit = defineEmits<{
    (e: "update:modelValue", value: File | null): void;
}>();

const input = ref<HTMLInputElement | null>(null);
const preview = ref<string | null>(null);

const updatePreview = () => {
    const photo = input.value?.files?.[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        preview.value = e.target?.result as string;
    };

    reader.readAsDataURL(photo);

    emit("update:modelValue", photo);
};

const clearPhoto = () => {
    preview.value = null;
    if (input.value) {
        input.value.value = "";
    }
    emit("update:modelValue", null);
};
</script>

<template>
    <div class="flex flex-col gap-2">
        <label
            v-if="label"
            :for="id"
            class="text-xs font-medium text-neutral-300"
        >
            {{ label }}
        </label>

        <div class="relative flex flex-col items-center">
            <!-- Dropzone / preview box -->
            <div
                class="group hover:border-primary/50 relative flex min-h-[140px] w-full cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-white/20 bg-white/[0.02] p-4 text-center transition-all duration-200 hover:bg-white/[0.04]"
                @click="input?.click()"
            >
                <input
                    :id="id"
                    ref="input"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="updatePreview"
                />

                <!-- Image preview -->
                <template v-if="preview">
                    <img
                        :src="preview"
                        alt="Preview"
                        class="max-h-48 w-full rounded-xl object-contain"
                    />
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 backdrop-blur-xs transition-opacity duration-200 group-hover:opacity-100"
                    >
                        <span class="pill-item text-xs text-white">
                            Alterar imagem
                        </span>
                    </div>
                </template>

                <!-- Empty state placeholder -->
                <template v-else>
                    <div
                        class="flex flex-col items-center gap-2 text-neutral-400"
                    >
                        <div
                            class="group-hover:border-primary/40 group-hover:text-primary flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-neutral-300 transition-colors"
                        >
                            <Upload :size="18" />
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-medium text-neutral-200">
                                Clique para selecionar imagem
                            </span>
                            <span class="text-[11px] text-neutral-400">
                                PNG, JPG ou WEBP
                            </span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Remove action if preview active -->
            <button
                v-if="preview"
                type="button"
                class="mt-2 text-xs text-neutral-400 transition-colors hover:text-red-400"
                @click.stop="clearPhoto"
            >
                Remover imagem selecionada
            </button>
        </div>

        <p v-if="errorMessage" class="text-xs text-red-400">
            {{ errorMessage }}
        </p>
    </div>
</template>
