<script setup lang="ts">
import type Participant from "@/Types/Participant";
import { onMounted, ref, watch } from "vue";
import { route } from "ziggy-js";

const props = defineProps<{ modelValue: string; errorMessage?: string }>();
const emit = defineEmits<{ (event: "update:modelValue", value: string): void }>();
const query = ref("");
const results = ref<Participant[]>([]);
let timer: ReturnType<typeof setTimeout> | undefined;
let requestId = 0;

const search = async (selectedId?: string) => {
    const id = ++requestId;
    const url = new URL(route("admin.staff.participants"), window.location.origin);
    if (selectedId) url.searchParams.set("participant_id", selectedId);
    else if (query.value.trim()) url.searchParams.set("query", query.value.trim());
    const response = await fetch(url, { headers: { Accept: "application/json" } });
    if (!response.ok) return;
    const participants = (await response.json()) as Participant[];
    if (id !== requestId) return;
    results.value = participants;
    const selected = participants.find((participant) => String(participant.id) === props.modelValue);
    if (selected) query.value = selected.user?.name ?? `Membro #${selected.id}`;
};

onMounted(() => void search(props.modelValue || undefined));
watch(query, () => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => void search(), 250);
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <label for="participant-search" class="mb-0.5 ml-3.5 text-xs font-medium text-neutral-400">Participante</label>
        <input
            id="participant-search"
            v-model="query"
            type="search"
            placeholder="Pesquisar participante…"
            class="pill-container w-full px-4 py-2 text-sm text-white placeholder-neutral-500 outline-none focus:border-white/20"
            :aria-invalid="!!errorMessage"
        />
        <select
            :value="modelValue"
            class="pill-container w-full px-4 py-2 text-sm text-white outline-none focus:border-white/20 [&>option]:bg-[#121319]"
            @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <option value="" disabled>Escolha um participante</option>
            <option v-for="participant in results" :key="participant.id" :value="participant.id">
                {{ participant.user?.name ?? `Membro #${participant.id}` }}
            </option>
        </select>
        <p v-if="errorMessage" class="ml-3 text-xs text-red-400">{{ errorMessage }}</p>
    </div>
</template>
