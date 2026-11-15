<script setup lang="ts">
import TextInput from "@/Components/Form/TextInput.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import CRUDModal from "@/Components/CRUD/CRUDModal.vue";
import type Edition from "@/Types/Edition";
import {
    arcadeGameSlugs,
    payoutConfigExamples,
} from "@/Pages/CRUD/ArcadeGames/arcadeGameOptions";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

interface Props {
    with: {
        editions: Edition[];
    };
}

defineProps<Props>();

const form = useForm({
    edition_id: "",
    slug: "",
    name: "",
    is_active: true,
    is_pvp: false,
    min_wager: "10",
    max_wager: "500",
    daily_win_cap: "",
    payout_config: "{}",
});

const submit = () => {
    form.post(route("admin.arcadeGames.store"));
};
</script>

<template>
    <CRUDModal
        title="Criar jogo do arcade"
        name="arcadeGames"
        :processing="form.processing"
        max-width="lg"
        @submit="submit"
    >
        <TextInput
            id="edition_id"
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
                :value="edition.id.toString()"
            >
                {{ edition.name }}
            </option>
        </TextInput>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                id="slug"
                v-model="form.slug"
                type="select"
                required
                label="Jogo"
                :error-message="form.errors.slug"
            >
                <option
                    v-for="slug in arcadeGameSlugs"
                    :key="slug.value"
                    :value="slug.value"
                >
                    {{ slug.label }}
                </option>
            </TextInput>

            <TextInput
                id="name"
                v-model="form.name"
                label="Nome"
                type="text"
                required
                autocomplete="name"
                :error-message="form.errors.name"
            />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <TextInput
                id="min_wager"
                v-model="form.min_wager"
                label="Aposta Mínima (Pontos)"
                type="number"
                required
                :error-message="form.errors.min_wager"
            />

            <TextInput
                id="max_wager"
                v-model="form.max_wager"
                label="Aposta Máxima (Pontos)"
                type="number"
                required
                :error-message="form.errors.max_wager"
            />
        </div>

        <TextInput
            id="daily_win_cap"
            v-model="form.daily_win_cap"
            label="Limite Diário de Ganhos (Pontos)"
            type="number"
            placeholder="Sem limite"
            :error-message="form.errors.daily_win_cap"
        />

        <div class="flex flex-col gap-2.5 pt-1">
            <div
                class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3"
            >
                <span class="text-xs font-medium text-neutral-300">
                    Jogo ativo?
                </span>
                <Checkbox id="is_active" v-model:checked="form.is_active" />
            </div>

            <div
                class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3"
            >
                <span class="text-xs font-medium text-neutral-300">
                    Jogo PvP?
                </span>
                <Checkbox id="is_pvp" v-model:checked="form.is_pvp" />
            </div>
        </div>

        <TextInput
            id="payout_config"
            v-model="form.payout_config"
            label="Configuração de Pagamentos (JSON)"
            type="textarea"
            required
            class="font-mono text-xs"
            placeholder='{"house_edge_pct": 10}'
            aria-describedby="payout_config-help"
            :error-message="form.errors.payout_config"
        />

        <details
            id="payout_config-help"
            class="rounded-2xl border border-white/10 bg-white/[0.02] px-4 py-3"
        >
            <summary
                class="cursor-pointer text-xs font-medium text-neutral-300 select-none"
            >
                Formatos esperados por jogo
            </summary>
            <p class="mt-2 text-[11px] text-neutral-500">
                As chaves em falta usam os valores por omissão do servidor.
            </p>
            <dl class="mt-2 space-y-2 font-mono text-[11px] leading-relaxed">
                <div
                    v-for="example in payoutConfigExamples"
                    :key="example.slug"
                >
                    <dt class="text-neutral-300">{{ example.slug }}</dt>
                    <dd class="break-words text-neutral-500">
                        {{ example.config }}
                    </dd>
                </div>
            </dl>
        </details>
    </CRUDModal>
</template>
