<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { computed, onMounted, ref, watch } from "vue";
import { route } from "ziggy-js";

const props = defineProps<{ modelValue: string; errorMessage?: string }>();
const emit = defineEmits<{ (event: "update:modelValue", value: string): void }>();
const query = ref("");
const results = ref<Participant[]>([]);
const open = ref(false);
const loading = ref(false);
const failed = ref(false);
let timer: ReturnType<typeof setTimeout> | undefined;
let requestId = 0;

const selected = computed(() => results.value.find((participant) => String(participant.id) === props.modelValue));

const search = async (selectedId?: string) => {
    const id = ++requestId;
    const url = new URL(route("admin.staff.participants"), window.location.origin);
    if (selectedId) url.searchParams.set("participant_id", selectedId);
    else if (query.value.trim()) url.searchParams.set("query", query.value.trim());
    loading.value = true;
    failed.value = false;
    try {
        const response = await fetch(url, { headers: { Accept: "application/json" } });
        if (!response.ok) throw new Error("Participant search failed");
        const participants = (await response.json()) as Participant[];
        if (id !== requestId) return;
        results.value = participants;
        if (selectedId) {
            const current = participants[0];
            if (current) query.value = current.user?.name ?? `Membro #${current.id}`;
        }
    } catch {
        if (id === requestId) failed.value = true;
    } finally {
        if (id === requestId) loading.value = false;
    }
};

const selectParticipant = (participant: Participant) => {
    emit("update:modelValue", String(participant.id));
    query.value = participant.user?.name ?? `Membro #${participant.id}`;
    open.value = false;
};

const onFocus = () => {
    open.value = true;
    if (!query.value || query.value === selected.value?.user?.name) void search();
};

onMounted(() => void search(props.modelValue || undefined));
watch(query, (value) => {
    if (!open.value) return;
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => void search(), 250);
});
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <label for="participant-search" class="ml-3.5 text-xs font-medium text-neutral-400">Participante</label>
        <div class="relative">
            <input
                id="participant-search"
                v-model="query"
                type="search"
                role="combobox"
                aria-autocomplete="list"
                :aria-expanded="open"
                aria-controls="participant-results"
                :aria-invalid="!!errorMessage"
                placeholder="Pesquisar por nome ou email…"
                class="pill-container w-full px-4 py-2 text-sm text-white placeholder-neutral-500 outline-none focus:border-white/20"
                @focus="onFocus"
                @keydown.esc="open = false"
                @keydown.down.prevent="open = true"
            />
            <div
                v-if="open"
                id="participant-results"
                role="listbox"
                class="absolute z-50 mt-2 max-h-64 w-full overflow-y-auto rounded-2xl border border-white/10 bg-[#121319] p-1.5 shadow-xl"
            >
                <p v-if="loading" class="px-3 py-2 text-sm text-neutral-400">A pesquisar…</p>
                <p v-else-if="failed" class="px-3 py-2 text-sm text-red-400">Não foi possível pesquisar participantes.</p>
                <p v-else-if="results.length === 0" class="px-3 py-2 text-sm text-neutral-400">Nenhum participante encontrado.</p>
                <button
                    v-for="participant in results"
                    :key="participant.id"
                    type="button"
                    role="option"
                    :aria-selected="String(participant.id) === modelValue"
                    class="flex w-full flex-col rounded-xl px-3 py-2 text-left hover:bg-white/10 focus:bg-white/10 focus:outline-none"
                    @click="selectParticipant(participant)"
                >
                    <span class="text-sm font-medium text-white">{{ participant.user?.name ?? `Membro #${participant.id}` }}</span>
                    <span class="text-xs text-neutral-400">{{ participant.user?.email }}</span>
                </button>
            </div>
        </div>
        <p v-if="errorMessage" class="ml-3 text-xs text-red-400">{{ errorMessage }}</p>
    </div>
</template>
