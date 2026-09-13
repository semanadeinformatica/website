<script setup lang="ts">
import { ref } from "vue";
import type Model from "@/Types/Model";
import Cell from "./Cell.vue";
import Modal from "@/Components/UI/Modal.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Pencil, Trash2 } from "@lucide/vue";

interface Props {
    item: Model;
    name: string;
}

const props = defineProps<Props>();

const deleteModalOpen = ref(false);
const isDeleting = ref(false);

const confirmDelete = () => {
    isDeleting.value = true;
    router.delete(route(`admin.${props.name}.destroy`, { id: props.item.id }), {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            deleteModalOpen.value = false;
        },
    });
};
</script>

<template>
    <tr
        class="border-b border-white/5 text-sm text-neutral-200 transition-colors duration-150 hover:bg-white/[0.04]"
    >
        <Cell>
            <span class="font-mono text-xs text-neutral-500">
                #{{ item.id }}
            </span>
        </Cell>

        <slot></slot>

        <Cell class="text-right">
            <div class="flex items-center justify-end gap-3 text-xs">
                <Link
                    :href="route(`admin.${name}.edit`, { id: item.id })"
                    class="inline-flex items-center gap-1 font-medium text-neutral-400 transition-colors hover:text-white"
                    title="Editar"
                >
                    <Pencil :size="12" />
                    <span>Editar</span>
                </Link>

                <span class="text-neutral-700">·</span>

                <button
                    type="button"
                    class="inline-flex items-center gap-1 font-medium text-neutral-400 transition-colors hover:text-red-400"
                    title="Eliminar"
                    @click="deleteModalOpen = true"
                >
                    <Trash2 :size="12" />
                    <span>Apagar</span>
                </button>
            </div>

            <!-- Delete Confirmation Modal -->
            <Modal
                v-model="deleteModalOpen"
                title="Eliminar Registo"
                description="Tem a certeza que deseja eliminar este registo? Esta ação é irreversível."
                max-width="md"
                @close="deleteModalOpen = false"
            >
                <div class="flex flex-col gap-4">
                    <div
                        class="rounded-2xl border border-red-500/20 bg-red-950/30 p-4 text-xs text-red-200"
                    >
                        O registo
                        <span class="font-mono font-bold">#{{ item.id }}</span>
                        será permanentemente removido da base de dados.
                    </div>

                    <div
                        class="mt-2 flex items-center justify-end gap-2.5 border-t border-white/10 pt-4"
                    >
                        <PrimaryButton
                            type="button"
                            color="pill"
                            text-size="text-xs"
                            :disabled="isDeleting"
                            @click="deleteModalOpen = false"
                        >
                            Cancelar
                        </PrimaryButton>
                        <PrimaryButton
                            type="button"
                            color="danger"
                            text-size="text-xs"
                            :disabled="isDeleting"
                            @click="confirmDelete"
                        >
                            {{
                                isDeleting
                                    ? "A eliminar..."
                                    : "Eliminar definitivamente"
                            }}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </Cell>
    </tr>
</template>
