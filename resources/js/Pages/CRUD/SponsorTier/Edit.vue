<script setup lang="ts">
import TextInput from "@/Components/Form/TextInput.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import type Edition from "@/Types/Edition";
import type SponsorTier from "@/Types/SponsorTier";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    with: {
        editions: Edition[];
    };
    item: SponsorTier;
}

const { item: tier } = defineProps<Props>();

const form = useForm({
    edition_id: tier.edition_id.toString(),
    name: tier.name,
    color: tier.color,
    rank: tier.rank.toString(),
    canSeeCV: tier.canSeeCV,
    canSeeLinkedin: tier.canSeeLinkedin,
    canSeeAll: tier.canSeeAll,
});

const submit = () => {
    form.put(route("admin.sponsorTiers.update", { sponsorTier: tier.id }));
};
</script>

<template>
    <CRUDModal
        title="Editar Nível de Patrocínio"
        name="sponsorTiers"
        :processing="form.processing"
        max-width="lg"
        @submit="submit"
    >
        <TextInput
            v-model="form.edition_id"
            type="select"
            required
            autofocus
            label="Edição"
            :error-message="form.errors.edition_id"
        >
            <option
                v-for="edition in $props.with.editions"
                :key="edition.id"
                :value="edition.id"
            >
                {{ edition.name }}
            </option>
        </TextInput>

        <TextInput
            id="name"
            v-model="form.name"
            label="Nome do Nível"
            type="text"
            required
            autocomplete="name"
            :error-message="form.errors.name"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                id="color"
                v-model="form.color"
                label="Cor"
                type="color"
                required
                :error-message="form.errors.color"
            />

            <TextInput
                id="rank"
                v-model="form.rank"
                label="Posição (Ordem)"
                type="number"
                required
                :error-message="form.errors.rank"
            />
        </div>

        <div class="flex flex-col gap-2.5 pt-1">
            <div
                class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3"
            >
                <span class="text-xs font-medium text-neutral-300">
                    Consegue ver os CVs dos participantes?
                </span>
                <Checkbox id="canSeeCV" v-model:checked="form.canSeeCV" />
            </div>

            <div
                class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3"
            >
                <span class="text-xs font-medium text-neutral-300">
                    Consegue ver o LinkedIn dos participantes?
                </span>
                <Checkbox
                    id="canSeeLinkedin"
                    v-model:checked="form.canSeeLinkedin"
                />
            </div>

            <div
                class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3"
            >
                <span class="text-xs font-medium text-neutral-300">
                    Consegue ver todos os participantes?
                </span>
                <Checkbox id="canSeeAll" v-model:checked="form.canSeeAll" />
            </div>
        </div>
    </CRUDModal>
</template>
